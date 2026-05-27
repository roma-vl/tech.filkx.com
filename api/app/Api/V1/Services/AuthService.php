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
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const TOKEN_NAME = 'api-access';

    private const TOKEN_EXPIRY_DAYS = 30;

    public function register(RegisterDto $dto): array
    {
        $user = User::create([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => Hash::make($dto->password),
            'locale'   => User::DEFAULT_LOCALE,
            'timezone' => 'Europe/Kyiv',
        ]);

        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        event(new Registered($user));

        $user->sendEmailVerificationNotification();

        $tokenDto = $this->createAccessToken($user);

        return [
            'token'   => $tokenDto->toArray(),
            'user'    => new UserResource($user),
            'message' => 'Registration successful. Please verify your email.',
        ];
    }

    public function login(LoginDto $dto): array
    {
        if (! Auth::attempt(['email' => $dto->email, 'password' => $dto->password])) {
            event(new AuditEvent(new AuditLogDto(
                action:    'auth.failed',
                domain:    'security',
                message:   "Failed login attempt for email: {$dto->email}",
                payload:   ['email' => $dto->email],
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

        $this->notifyIfNewDevice($user);

        $tokenDto = $this->createAccessToken($user);

        event(new AuditEvent(new AuditLogDto(
            action:    'auth.login',
            domain:    'security',
            message:   "User {$user->name} logged in successfully",
            userId:    $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));

        return [
            'token' => $tokenDto->toArray(),
            'user'  => new UserResource($user),
        ];
    }

    public function logout(User $user): void
    {
        event(new AuditEvent(new AuditLogDto(
            action:    'auth.logout',
            domain:    'security',
            message:   "User {$user->name} logged out",
            userId:    $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));

        $user->token()?->revoke();
    }

    public function refreshToken(User $user): array
    {
        $currentToken   = $user->token();
        $impersonatorId = null;

        if ($currentToken) {
            $dbToken        = DB::table('oauth_access_tokens')
                ->where('id', $currentToken->id)
                ->first();
            $impersonatorId = $dbToken?->impersonator_id;
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
                'email'    => $dto->email,
                'password' => $dto->password,
                'token'    => $dto->token,
            ],
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
                $user->tokens()->delete();
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
        $tokenDto = $this->createAccessToken($user);

        $this->notifyIfNewDevice($user);

        event(new AuditEvent(new AuditLogDto(
            action:    'auth.login_oauth',
            domain:    'security',
            message:   "User {$user->name} logged in via OAuth",
            userId:    $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));

        return [
            'token' => $tokenDto->toArray(),
            'user'  => new UserResource($user),
        ];
    }

    private function createAccessToken(User $user, ?int $impersonatorId = null): AuthTokenDto
    {
        $tokenResult = $user->createToken(self::TOKEN_NAME);
        $token       = $tokenResult->token;

        if ($impersonatorId) {
            $token->impersonator_id = $impersonatorId;
        }

        $expiresAt       = now()->addDays(config('auth.api_token_expired_in_days', self::TOKEN_EXPIRY_DAYS));
        $token->expires_at = $expiresAt;
        $token->save();

        $expiresIn = $expiresAt->copy()->setTimezone('UTC')->timestamp - now()->setTimezone('UTC')->timestamp;

        return new AuthTokenDto(
            accessToken: $tokenResult->accessToken,
            tokenType:   'Bearer',
            expiresIn:   $expiresIn,
            expiresAt:   $expiresAt->toIso8601String()
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
                location:   $currentIp,
                time:       now()->toDateTimeString()
            ));
        }
    }

    private function parseDeviceName(string $ua): string
    {
        // Simple UA-based device name without external packages
        $browser  = 'Unknown Browser';
        $platform = 'Unknown OS';

        if (str_contains($ua, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'Edg')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Safari')) {
            $browser = 'Safari';
        }

        if (str_contains($ua, 'Windows')) {
            $platform = 'Windows';
        } elseif (str_contains($ua, 'Macintosh')) {
            $platform = 'macOS';
        } elseif (str_contains($ua, 'Linux')) {
            $platform = 'Linux';
        } elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $platform = 'iOS';
        } elseif (str_contains($ua, 'Android')) {
            $platform = 'Android';
        }

        return "{$browser} on {$platform}";
    }
}
