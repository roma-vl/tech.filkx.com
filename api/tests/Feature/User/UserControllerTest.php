<?php

namespace Tests\Feature\User;

use App\Api\V1\Controllers\UserController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\AccountDeletionScheduledNotification;
use App\Notifications\AccountRestoredNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    private function signedRestoreUrl(int $userId): string
    {
        return URL::signedRoute('user.restore', ['userId' => $userId]);
    }

    /**
     * `confirmEmailChange` is not wired to any route yet (see the controller's own OA docs)
     * - the route is registered here only so the signed-link behaviour can be exercised at
     * the HTTP layer, matching what the route will look like once an email-change
     * notification actually issues these links.
     */
    private function signedConfirmEmailChangeUrl(int $userId, string $newEmail): string
    {
        Route::get('/api/user/email/confirm-change', [UserController::class, 'confirmEmailChange'])
            ->name('test.user.confirm-email-change');
        Route::getRoutes()->refreshNameLookups();

        return URL::signedRoute('test.user.confirm-email-change', ['id' => $userId, 'new_email' => $newEmail]);
    }

    private function makeProduct(string $status = 'active'): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);
    }

    private function makeOrder(User $user, string $status, array $attributes = []): Order
    {
        $product = $this->makeProduct();
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => 100,
        ]);

        $order = Order::create(array_merge([
            'order_number' => 'FKX-'.uniqid(),
            'user_id' => $user->id,
            'customer_name' => 'Іван Петренко',
            'customer_email' => $user->email,
            'customer_phone' => '+380501112233',
            'shipping_address' => 'вул. Хрещатик, 1',
            'delivery_method' => 'nova_poshta',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => $status,
            'total_price' => 100,
            'discount_amount' => 0,
        ], $attributes));

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => 'Товар',
            'sku' => $variant->sku,
            'price' => 100,
            'quantity' => 1,
        ]);

        return $order;
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/user/me')->assertUnauthorized();
    }

    // --- Notification preferences ---

    public function test_get_preferences_returns_defaults_when_unset(): void
    {
        $user = User::factory()->create(['notification_preferences' => null]);

        $response = $this->withHeaders($this->authHeader($user))->getJson('/api/user/settings/preferences');

        $response->assertOk()->assertJsonPath('data.preferences.newsletter', false);
    }

    public function test_update_preferences_persists_the_new_values(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))->putJson('/api/user/settings/preferences', [
            'newsletter' => true,
            'productUpdates' => false,
            'marketingEmails' => true,
        ]);

        $response->assertOk()->assertJsonPath('data.preferences.newsletter', true);
        $this->assertSame('true', json_encode($user->fresh()->notification_preferences['newsletter']));
    }

    // --- Favorites ---

    public function test_get_favorites_returns_the_users_favorited_products(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->favorites()->attach($product->id);

        $response = $this->withHeaders($this->authHeader($user))->getJson('/api/user/favorites');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_toggle_favorite_requires_a_product_id(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/favorites/toggle', [])
            ->assertStatus(400);
    }

    public function test_toggle_favorite_returns_404_for_unknown_product(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/favorites/toggle', ['product_id' => 999999])
            ->assertStatus(404);
    }

    public function test_toggle_favorite_adds_then_removes_the_product(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $headers = $this->authHeader($user);

        $this->withHeaders($headers)
            ->postJson('/api/user/favorites/toggle', ['product_id' => $product->id])
            ->assertOk()->assertJsonCount(1, 'data');

        $this->withHeaders($headers)
            ->postJson('/api/user/favorites/toggle', ['product_id' => $product->id])
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_sync_favorites_merges_the_given_product_ids(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/favorites/sync', ['product_ids' => [$product->id]]);

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    // --- Compares ---

    public function test_toggle_compare_requires_a_product_id(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/compares/toggle', [])
            ->assertStatus(400);
    }

    public function test_toggle_compare_adds_the_product(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/compares/toggle', ['product_id' => $product->id]);

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_sync_compares_merges_the_given_product_ids(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/compares/sync', ['product_ids' => [$product->id]]);

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    // --- Viewed products ---

    public function test_track_viewed_product_requires_a_product_id(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/viewed-products/track', [])
            ->assertStatus(400);
    }

    public function test_track_then_get_viewed_products(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $headers = $this->authHeader($user);

        $this->withHeaders($headers)
            ->postJson('/api/user/viewed-products/track', ['product_id' => $product->id])
            ->assertOk();

        $this->withHeaders($headers)
            ->getJson('/api/user/viewed-products')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_clear_viewed_products_empties_the_history(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->viewedProducts()->attach($product->id, ['view_count' => 1]);
        $headers = $this->authHeader($user);

        $this->withHeaders($headers)->deleteJson('/api/user/viewed-products/clear')->assertOk();

        $this->withHeaders($headers)
            ->getJson('/api/user/viewed-products')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    // --- Orders ---

    public function test_get_orders_returns_the_users_order_history_formatted(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, 'pending');

        $response = $this->withHeaders($this->authHeader($user))->getJson('/api/user/orders');

        $response->assertOk()
            ->assertJsonPath('data.0.dbId', $order->id)
            ->assertJsonPath('data.0.statusCode', 'pending');
    }

    public function test_cancel_order_requires_authentication(): void
    {
        $this->postJson('/api/user/orders/1/cancel')->assertUnauthorized();
    }

    public function test_cancel_order_returns_404_for_unknown_order(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/orders/999999/cancel')
            ->assertStatus(404);
    }

    public function test_cancel_order_returns_403_when_order_belongs_to_another_user(): void
    {
        $owner = User::factory()->create();
        $order = $this->makeOrder($owner, 'pending');
        $stranger = User::factory()->create(['email' => 'stranger@example.com']);

        $this->withHeaders($this->authHeader($stranger))
            ->postJson("/api/user/orders/{$order->id}/cancel")
            ->assertStatus(403);
    }

    public function test_cancel_order_returns_422_when_order_already_shipped(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, 'shipped');

        $this->withHeaders($this->authHeader($user))
            ->postJson("/api/user/orders/{$order->id}/cancel")
            ->assertStatus(422);
    }

    public function test_cancel_order_returns_400_when_already_cancelled(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, 'cancelled');

        $this->withHeaders($this->authHeader($user))
            ->postJson("/api/user/orders/{$order->id}/cancel")
            ->assertStatus(400);
    }

    public function test_cancel_order_succeeds_for_a_cancellable_order_owned_by_the_user(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, 'pending_payment');

        $this->withHeaders($this->authHeader($user))
            ->postJson("/api/user/orders/{$order->id}/cancel")
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_download_invoice_returns_404_for_unknown_order(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/user/orders/999999/invoice')
            ->assertStatus(404);
    }

    public function test_download_invoice_returns_403_when_order_belongs_to_another_user(): void
    {
        $owner = User::factory()->create();
        $order = $this->makeOrder($owner, 'pending');
        $stranger = User::factory()->create(['email' => 'stranger@example.com']);

        $this->withHeaders($this->authHeader($stranger))
            ->getJson("/api/user/orders/{$order->id}/invoice")
            ->assertStatus(403);
    }

    public function test_download_invoice_streams_a_pdf_for_the_orders_owner(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, 'pending');

        $response = $this->withHeaders($this->authHeader($user))
            ->get("/api/user/orders/{$order->id}/invoice");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    // --- me ---

    public function test_me_returns_the_authenticated_users_details(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))->getJson('/api/user/me');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    // --- Locale ---

    public function test_update_locale_persists_the_new_locale(): void
    {
        $user = User::factory()->create(['locale' => 'uk']);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/locale', ['locale' => 'en']);

        $response->assertOk()->assertJsonPath('data.locale', 'en');
        $this->assertSame('en', $user->fresh()->locale);
    }

    public function test_update_locale_rejects_an_unsupported_value(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/locale', ['locale' => 'fr'])
            ->assertStatus(422);
    }

    // --- Profile ---

    public function test_update_profile_persists_the_new_details(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))->putJson('/api/user/profile', [
            'name' => 'New Name',
            'email' => $user->email,
            'phone' => '+380501234567',
            'language' => 'uk',
            'addresses' => [['city' => 'Kyiv']],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Profile updated successfully')
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.phone', '+380501234567');
        $this->assertSame('New Name', $user->fresh()->name);
    }

    public function test_update_profile_rejects_an_email_already_taken_by_another_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->putJson('/api/user/profile', ['name' => $user->name, 'email' => $other->email])
            ->assertStatus(422);
    }

    public function test_update_profile_requires_name_and_email(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->putJson('/api/user/profile', [])
            ->assertStatus(422);
    }

    // --- Password ---

    public function test_update_password_changes_the_password_when_current_password_is_correct(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))->putJson('/api/user/password', [
            'currentPassword' => 'password',
            'newPassword' => 'new-secure-password',
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    public function test_update_password_rejects_an_incorrect_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))->putJson('/api/user/password', [
            'currentPassword' => 'wrong-password',
            'newPassword' => 'new-secure-password',
        ]);

        $response->assertStatus(400)->assertJsonPath('message', 'Current password is incorrect');
    }

    public function test_update_password_requires_a_minimum_length_new_password(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->putJson('/api/user/password', ['currentPassword' => 'password', 'newPassword' => 'short'])
            ->assertStatus(422);
    }

    // --- Avatar ---

    public function test_upload_avatar_stores_the_file_and_updates_the_user(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))
            ->post('/api/user/avatar', ['avatar' => UploadedFile::fake()->image('avatar.jpg')]);

        $response->assertOk();
        $this->assertNotNull($user->fresh()->avatar_path);
        Storage::disk('public')->assertExists($user->fresh()->avatar_path);
    }

    public function test_upload_avatar_rejects_a_non_image_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $headers = array_merge($this->authHeader($user), ['Accept' => 'application/json']);

        $this->withHeaders($headers)
            ->post('/api/user/avatar', ['avatar' => UploadedFile::fake()->create('avatar.txt', 10)])
            ->assertStatus(422);
    }

    public function test_upload_avatar_rejects_a_file_over_the_size_limit(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $headers = array_merge($this->authHeader($user), ['Accept' => 'application/json']);

        $this->withHeaders($headers)
            ->post('/api/user/avatar', ['avatar' => UploadedFile::fake()->image('avatar.jpg')->size(2049)])
            ->assertStatus(422);
    }

    public function test_delete_avatar_removes_the_stored_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/existing.jpg', 'fake-content');
        $user = User::factory()->create(['avatar_path' => 'avatars/existing.jpg']);

        $response = $this->withHeaders($this->authHeader($user))->deleteJson('/api/user/avatar');

        $response->assertOk()->assertJsonPath('data.avatarUrl', null);
        Storage::disk('public')->assertMissing('avatars/existing.jpg');
        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_delete_avatar_is_a_no_op_when_no_avatar_is_set(): void
    {
        $user = User::factory()->create(['avatar_path' => null]);

        $this->withHeaders($this->authHeader($user))
            ->deleteJson('/api/user/avatar')
            ->assertOk()
            ->assertJsonPath('data.avatarUrl', null);
    }

    // --- Set password (OAuth users) ---

    public function test_set_password_sets_a_password_for_an_oauth_user_without_one(): void
    {
        $user = User::factory()->create(['password' => Hash::make('')]);
        $user->oauthAccounts()->create([
            'provider' => 'google',
            'provider_user_id' => 'g-123',
            'email' => $user->email,
        ]);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/password/set', ['password' => 'brand-new-password']);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertTrue(Hash::check('brand-new-password', $user->fresh()->password));
    }

    public function test_set_password_rejects_when_a_password_is_already_set(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/password/set', ['password' => 'another-password'])
            ->assertStatus(400);
    }

    public function test_set_password_rejects_when_the_account_has_no_oauth_provider(): void
    {
        $user = User::factory()->create(['password' => Hash::make('')]);

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/password/set', ['password' => 'another-password'])
            ->assertStatus(403);
    }

    public function test_set_password_requires_a_minimum_length_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('')]);
        $user->oauthAccounts()->create([
            'provider' => 'google',
            'provider_user_id' => 'g-123',
            'email' => $user->email,
        ]);

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/password/set', ['password' => 'short'])
            ->assertStatus(422);
    }

    // --- Sessions ---

    public function test_sessions_lists_only_active_tokens(): void
    {
        $user = User::factory()->create();
        $headers = $this->authHeader($user);
        $revoked = $user->createToken('revoked-session')->token;
        $revoked->revoked = true;
        $revoked->save();
        $user->createToken('another-active-session');

        $response = $this->withHeaders($headers)->getJson('/api/user/sessions');

        $response->assertOk()->assertJsonCount(2, 'data.sessions');
    }

    public function test_logout_all_revokes_every_session_except_the_current_one(): void
    {
        $user = User::factory()->create();
        $headers = $this->authHeader($user);
        $currentToken = $user->tokens()->where('revoked', false)->first();
        $other = $user->createToken('other-session')->token;

        $response = $this->withHeaders($headers)->postJson('/api/user/sessions/logout-all');

        $response->assertOk()->assertJsonPath('data.revokedCount', 1);
        $this->assertTrue($other->fresh()->revoked);
        $this->assertFalse($currentToken->fresh()->revoked);
    }

    // --- Account deletion ---

    public function test_initiate_delete_soft_deletes_the_account(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))->postJson('/api/user/delete');

        $response->assertOk();
        $this->assertSoftDeleted($user);
    }

    public function test_initiate_delete_sends_an_account_deletion_scheduled_notification_with_a_restore_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader($user))->postJson('/api/user/delete');

        Notification::assertSentTo($user, AccountDeletionScheduledNotification::class, function (AccountDeletionScheduledNotification $notification) use ($user) {
            return str_contains($notification->restoreUrl, "userId={$user->id}")
                && str_contains($notification->restoreUrl, 'signature=');
        });
    }

    // --- Account restore ---

    public function test_restore_restores_a_soft_deleted_account_and_redirects(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $user->delete();

        $response = $this->get($this->signedRestoreUrl($user->id));

        $response->assertRedirect(config('app.frontend_url').'/login?status=restored');
        $this->assertNotSoftDeleted($user);
    }

    public function test_restore_sends_an_account_restored_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $user->delete();

        $this->get($this->signedRestoreUrl($user->id));

        Notification::assertSentTo($user, AccountRestoredNotification::class);
    }

    public function test_restore_returns_400_for_an_unknown_user(): void
    {
        $response = $this->get($this->signedRestoreUrl(999999));

        $response->assertStatus(400);
    }

    public function test_restore_rejects_an_invalid_signature(): void
    {
        $response = $this->get('/api/user/restore?userId=1&signature=invalid');

        $response->assertStatus(400)->assertJsonPath('message', 'Invalid or expired restoration link');
    }

    // --- Email change confirmation (signed link; see signedConfirmEmailChangeUrl() for why the route is registered here) ---

    public function test_confirm_email_change_updates_the_email_and_redirects(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'old@example.com']);

        $response = $this->get($this->signedConfirmEmailChangeUrl($user->id, 'new@example.com'));

        $response->assertRedirect(config('app.frontend_url').'/login?status=email-changed');
        $user->refresh();
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_confirm_email_change_redirects_with_an_error_when_the_email_is_taken(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->get($this->signedConfirmEmailChangeUrl($user->id, 'taken@example.com'));

        $response->assertRedirect(config('app.frontend_url').'/settings?error=email_taken');
        $this->assertSame('old@example.com', $user->fresh()->email);
    }

    public function test_confirm_email_change_rejects_an_invalid_signature(): void
    {
        Route::get('/api/user/email/confirm-change', [UserController::class, 'confirmEmailChange']);

        $this->get('/api/user/email/confirm-change?id=1&new_email=x@example.com&signature=invalid')
            ->assertStatus(403);
    }

    // --- Compares ---

    public function test_get_compares_returns_the_users_compared_products(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->compares()->attach($product->id);

        $response = $this->withHeaders($this->authHeader($user))->getJson('/api/user/compares');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    // --- Viewed products sync ---

    public function test_sync_viewed_products_creates_new_entries(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/viewed-products/sync', [
                'items' => [
                    ['id' => $product->id, 'view_count' => 5, 'last_viewed_at' => now()->toISOString()],
                ],
            ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.viewCount', 5);
    }

    public function test_sync_viewed_products_merges_an_existing_entry_keeping_the_higher_view_count(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->viewedProducts()->attach($product->id, ['view_count' => 10]);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/user/viewed-products/sync', [
                'items' => [
                    ['id' => $product->id, 'view_count' => 3, 'last_viewed_at' => now()->toISOString()],
                ],
            ]);

        $response->assertOk()->assertJsonPath('data.0.viewCount', 10);
    }
}
