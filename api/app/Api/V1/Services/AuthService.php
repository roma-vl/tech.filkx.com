<?php

namespace App\Api\V1\Services;

use App\Api\V1\Dto\AuditLogDto;
use App\Api\V1\Dto\Auth\AuthTokenDto;
use App\Api\V1\Dto\Auth\ForgotPasswordDto;
use App\Api\V1\Dto\Auth\LoginDto;
use App\Api\V1\Dto\Auth\RegisterDto;
use App\Api\V1\Dto\Auth\ResetPasswordDto;
use App\Api\V1\Resources\User\UserResource;
use App\Events\AuditEvent;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Notifications\LoginNewDeviceNotification;
use App\Notifications\PasswordChangedNotification;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;

class AuthService
{
    private const TOKEN_NAME = 'api-access';

    private const TOKEN_EXPIRY_DAYS = 30;

    private const TWO_FACTOR_CHALLENGE_TTL_MINUTES = 5;

    private const TWO_FACTOR_CHALLENGE_CACHE_PREFIX = 'auth.2fa_challenge.';

    public function __construct(
        private readonly TwoFactorAuthenticationService $twoFactor,
    ) {}

    public function register(RegisterDto $dto): array
    {
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'locale' => User::DEFAULT_LOCALE,
            'timezone' => 'Europe/Kyiv',
        ]);

        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        event(new Registered($user));

        $user->sendEmailVerificationNotification();

        $tokenDto = $this->createAccessToken($user);

        return [
            'token' => $tokenDto->toArray(),
            'user' => new UserResource($user),
            'message' => 'Registration successful. Please verify your email.',
        ];
    }

    public function login(LoginDto $dto): array
    {
        if (! Auth::attempt(['email' => $dto->email, 'password' => $dto->password])) {
            event(new AuditEvent(new AuditLogDto(
                action: 'auth.failed',
                domain: 'security',
                message: "Failed login attempt for email: {$dto->email}",
                payload: ['email' => $dto->email],
                ipAddress: request()->ip(),
                userAgent: request()->userAgent()
            )));

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $dto->email)
            ->with('roles.permissions')
            ->firstOrFail();

        if ($user->hasTwoFactorEnabled()) {
            return $this->issueTwoFactorChallenge($user);
        }

        $this->notifyIfNewDevice($user);

        $tokenDto = $this->createAccessToken($user);

        event(new AuditEvent(new AuditLogDto(
            action: 'auth.login',
            domain: 'security',
            message: "User {$user->name} logged in successfully",
            userId: $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));

        return [
            'token' => $tokenDto->toArray(),
            'user' => new UserResource($user),
        ];
    }

    /**
     * Completes a login that was paused for two-factor verification. Accepts either a
     * current TOTP code or an unused recovery code (which is burned on success).
     */
    public function verifyTwoFactorChallenge(string $challengeToken, string $code): array
    {
        $cacheKey = self::TWO_FACTOR_CHALLENGE_CACHE_PREFIX.$challengeToken;
        $userId = Cache::get($cacheKey);

        if (! $userId) {
            throw ValidationException::withMessages([
                'challenge_token' => ['This login confirmation has expired. Please log in again.'],
            ]);
        }

        $user = User::where('id', $userId)->with('roles.permissions')->firstOrFail();

        if (! $this->verifyTwoFactorCodeOrRecoveryCode($user, $code)) {
            throw ValidationException::withMessages([
                'code' => ['The provided code is invalid.'],
            ]);
        }

        // One-time use: a challenge token must not be replayable against a second code attempt.
        Cache::forget($cacheKey);

        $this->notifyIfNewDevice($user);

        $tokenDto = $this->createAccessToken($user);

        event(new AuditEvent(new AuditLogDto(
            action: 'auth.login',
            domain: 'security',
            message: "User {$user->name} logged in successfully (2FA)",
            userId: $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));

        return [
            'token' => $tokenDto->toArray(),
            'user' => new UserResource($user),
        ];
    }

    public function logout(User $user): void
    {
        event(new AuditEvent(new AuditLogDto(
            action: 'auth.logout',
            domain: 'security',
            message: "User {$user->name} logged out",
            userId: $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));

        $user->token()?->revoke();
    }

    public function refreshToken(User $user): array
    {
        $currentToken = $user->token();
        $impersonatorId = null;

        if ($currentToken) {
            $dbToken = DB::table('oauth_access_tokens')
                ->where('id', $currentToken->id)
                ->first();
            $impersonatorId = $dbToken && isset($dbToken->impersonator_id) ? $dbToken->impersonator_id : null;
            $currentToken->revoke();
        }

        $tokenDto = $this->createAccessToken($user, $impersonatorId);

        return [
            'token' => $tokenDto->toArray(),
        ];
    }

    public function verifyEmail(string $id, string $hash, string $expires, string $signature): string
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return 'Email already verified';
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid verification link.'],
            ]);
        }

        $user->markEmailAsVerified();

        return 'Email verified successfully';
    }

    public function resendVerificationEmail(string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Email already verified.'],
            ]);
        }

        $user->sendEmailVerificationNotification();
    }

    public function sendResetLink(ForgotPasswordDto $dto): void
    {
        $status = Password::sendResetLink(['email' => $dto->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    public function resetPassword(ResetPasswordDto $dto): void
    {
        $status = Password::reset(
            [
                'email' => $dto->email,
                'password' => $dto->password,
                'token' => $dto->token,
            ],
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
                $user->tokens()->delete();
                $user->notify(new PasswordChangedNotification(request()->ip() ?? 'unknown', now()->toDateTimeString()));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    public function createLoginResponse(User $user): array
    {
        if ($user->hasTwoFactorEnabled()) {
            return $this->issueTwoFactorChallenge($user);
        }

        $tokenDto = $this->createAccessToken($user);

        $this->notifyIfNewDevice($user);

        event(new AuditEvent(new AuditLogDto(
            action: 'auth.login_oauth',
            domain: 'security',
            message: "User {$user->name} logged in via OAuth",
            userId: $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));

        return [
            'token' => $tokenDto->toArray(),
            'user' => new UserResource($user),
        ];
    }

    private function createAccessToken(User $user, ?int $impersonatorId = null): AuthTokenDto
    {
        $tokenResult = $user->createToken(self::TOKEN_NAME);
        $token = $tokenResult->token;

        if ($impersonatorId) {
            $token->impersonator_id = $impersonatorId;
        }

        $expiresAt = now()->addDays(config('auth.api_token_expired_in_days', self::TOKEN_EXPIRY_DAYS));
        $token->expires_at = $expiresAt;
        $token->save();

        $expiresIn = $expiresAt->copy()->setTimezone('UTC')->timestamp - now()->setTimezone('UTC')->timestamp;

        return new AuthTokenDto(
            accessToken: $tokenResult->accessToken,
            tokenType: 'Bearer',
            expiresIn: $expiresIn,
            expiresAt: $expiresAt->toIso8601String()
        );
    }

    private function notifyIfNewDevice(User $user): void
    {
        $currentIp = request()->ip();
        $currentUa = request()->userAgent() ?? '';

        // Check last 10 auth logs for same IP + same UA prefix (browser signature)
        $recentLogins = AuditLog::where('user_id', $user->id)
            ->whereIn('action', ['auth.login', 'auth.login_oauth'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $isNew = true;
        foreach ($recentLogins as $log) {
            if ($log->ip_address === $currentIp && $log->user_agent === $currentUa) {
                $isNew = false;
                break;
            }
        }

        if ($isNew) {
            $user->notify(new LoginNewDeviceNotification(
                deviceName: $this->parseDeviceName($currentUa),
                location: $currentIp,
                time: now()->toDateTimeString()
            ));
        }
    }

    private function parseDeviceName(string $ua): string
    {
        $agent = new Agent;
        $agent->setUserAgent($ua);

        $browser = $agent->browser() ?: 'Unknown Browser';
        $platform = $agent->platform() ?: 'Unknown OS';

        return "{$browser} on {$platform}";
    }

    /**
     * Password (or OAuth) check passed but the account has 2FA enabled — pause the login
     * behind a short-lived, single-use challenge instead of issuing a real access token.
     */
    private function issueTwoFactorChallenge(User $user): array
    {
        $challengeToken = Str::random(64);

        Cache::put(
            self::TWO_FACTOR_CHALLENGE_CACHE_PREFIX.$challengeToken,
            $user->id,
            now()->addMinutes(self::TWO_FACTOR_CHALLENGE_TTL_MINUTES)
        );

        event(new AuditEvent(new AuditLogDto(
            action: 'auth.2fa_challenge_issued',
            domain: 'security',
            message: "User {$user->name} passed credential check, awaiting 2FA code",
            userId: $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));

        return [
            'two_factor_required' => true,
            'challenge_token' => $challengeToken,
        ];
    }

    private function verifyTwoFactorCodeOrRecoveryCode(User $user, string $code): bool
    {
        if ($this->twoFactor->verifyCode($user->two_factor_secret, $code)) {
            return true;
        }

        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        $normalizedCode = Str::upper(trim($code));

        if (! in_array($normalizedCode, $recoveryCodes, true)) {
            return false;
        }

        // Recovery codes are one-time use — burn it immediately on successful use.
        $user->update([
            'two_factor_recovery_codes' => array_values(array_diff($recoveryCodes, [$normalizedCode])),
        ]);

        event(new AuditEvent(new AuditLogDto(
            action: 'auth.2fa_recovery_code_used',
            domain: 'security',
            message: "User {$user->name} logged in using a two-factor recovery code",
            userId: $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));

        return true;
    }
}
