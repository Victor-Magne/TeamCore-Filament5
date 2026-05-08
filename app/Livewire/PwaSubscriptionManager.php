<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PwaSubscriptionManager extends Component
{
    public bool $isSubscribed = false;

    public function mount()
    {
        // Check if user has any push subscriptions in DB
        $this->isSubscribed = Auth::user()->pushSubscriptions()->exists();
    }

    public function updateSubscription($subscription)
    {
        Auth::user()->updatePushSubscription(
            $subscription['endpoint'],
            $subscription['publicKey'] ?? null,
            $subscription['authToken'] ?? null,
            $subscription['contentEncoding'] ?? null
        );

        $this->isSubscribed = true;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Notificações activadas com sucesso!'
        ]);
    }

    public function deleteSubscription($endpoint)
    {
        Auth::user()->deletePushSubscription($endpoint);
        $this->isSubscribed = false;

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Notificações desactivadas.'
        ]);
    }

    public function render()
    {
        return view('livewire.pwa-subscription-manager');
    }
}
