<?php

namespace App\Api\V1\Services;

use App\Api\V1\Dto\AuditLogDto;
use App\Api\V1\Dto\Auth\AuthTokenDto;
use App\Api\V1\Dto\Auth\ForgotPasswordDto;
use App\Api\V1\Dto\Auth\LoginDto;
use App\Api\V1\Dto\Auth\RegisterDto;
use App\Api\V1\Dto\Auth\ResetPasswordDto;
use App\Api\V1\Resources\User\UserResource;
use App\Core\Growth\Domain\Services\AttributionManager;
use App\Events\AuditEvent;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Notifications\LoginNewDeviceNotification;
use App\Services\Affiliate\AffiliateService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;

class AuthService
{
    private const TOKEN_NAME = 'api-access';

    private const TOKEN_EXPIRY_DAYS = 30;

    public function __construct(
        private readonly AffiliateService   $affiliateService,
        private readonly AttributionManager $attributionManager
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

        // Subscription will be handled manually by the user
        // app(AssignTrialSubscriptionAction::class)->execute($user);

        if ($dto->affiliateRef) {
            $this->affiliateService->trackReferral($user, $dto->affiliateRef);
        } elseif ($ref = request()->cookie('affiliate_ref')) {
            $this->affiliateService->trackReferral($user, $ref);
        }

        $this->attributionManager->bindUser($user);

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
                'email' => $dto->email,
                'password' => $dto->password,
                'token' => $dto->token,
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

    public function createLoginResponse(User $user): array
    {
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

        $this->attributionManager->bindUser($user);

        return [
            'token' => $tokenDto->toArray(),
            'user' => new UserResource($user),
        ];
    }

    private function notifyIfNewDevice(User $user): void
    {
        $agent = new Agent;
        $browser = $agent->browser();
        $platform = $agent->platform();
        $deviceName = "{$browser} on {$platform}";

        $currentIp = request()->ip();

        // Simple check: is this combination of IP and UA in the last 10 audit logs?
        $recentLogins = AuditLog::where('user_id', $user->id)
            ->whereIn('action', ['auth.login', 'auth.login_oauth'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $isNew = true;
        foreach ($recentLogins as $log) {
            $logAgent = new Agent;
            $logAgent->setUserAgent($log->user_agent);

            if ($log->ip_address === $currentIp &&
                $logAgent->browser() === $browser &&
                $logAgent->platform() === $platform
            ) {
                $isNew = false;
                break;
            }
        }

        // If no login history yet, we also treat it as "new" to be safe (or first login)
        if ($isNew) {
            $user->notify(new LoginNewDeviceNotification(
                deviceName: $deviceName,
                location: $currentIp, // In a real app, use GeoIP here
                time: now()->toDateTimeString()
            ));
        }
    }


}
