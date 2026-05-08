<?php

namespace App\Models\Concerns;

use App\Models\PushSubscription;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPushSubscriptions
{
    public function pushSubscriptions(): MorphMany
    {
        return $this->morphMany(PushSubscription::class, 'subscribable');
    }

    public function updatePushSubscription(
        string $endpoint,
        ?string $publicKey = null,
        ?string $authToken = null,
        ?string $contentEncoding = null
    ): PushSubscription {
        /** @var PushSubscription $subscription */
        $subscription = $this->pushSubscriptions()->updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'public_key' => $publicKey,
                'auth_token' => $authToken,
                'content_encoding' => $contentEncoding,
            ]
        );

        return $subscription;
    }

    public function deletePushSubscription(string $endpoint): int
    {
        return $this->pushSubscriptions()
            ->where('endpoint', $endpoint)
            ->delete();
    }
}
