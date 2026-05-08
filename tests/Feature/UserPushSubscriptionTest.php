<?php

use App\Models\User;

it('creates and deletes a user push subscription', function (): void {
    $user = User::factory()->create([
        'employee_id' => null,
    ]);

    $subscription = $user->updatePushSubscription(
        'https://example.test/subscriptions/1',
        'public-key',
        'auth-token',
        'aes128gcm'
    );

    expect($subscription->endpoint)->toBe('https://example.test/subscriptions/1');
    expect($user->pushSubscriptions()->exists())->toBeTrue();

    $user->deletePushSubscription('https://example.test/subscriptions/1');

    expect($user->pushSubscriptions()->exists())->toBeFalse();
});
