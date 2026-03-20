<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use Carbon\Carbon;

class KitchenView extends Component
{
    public $pendingOrders = [];
    public $estimatedTime = 0;
    public $lastOrderCount = 0;

    public function mount()
    {
        $this->updatePendingOrders(true);
    }

    public function updatePendingOrders($isMount = false)
    {
        $this->pendingOrders = Order::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();

        $currentCount = count($this->pendingOrders);

        // If not initial mount and count increased, display alert
        if (!$isMount && $currentCount > $this->lastOrderCount) {
            $this->dispatch('new-order-alert');
        }
        
        $this->lastOrderCount = $currentCount;

        $totalItems = 0;
        foreach ($this->pendingOrders as $order) {
            foreach ($order['items'] as $item) {
                $totalItems += $item['quantity'];
            }
        }
        $this->estimatedTime = ($totalItems * 5) + 5;
    }

    public function markAsReady($id)
    {
        $order = Order::find($id);
        if ($order) {
            $order->status = 'ready';
            $order->save();
        }
        $this->updatePendingOrders();
        $this->dispatch('notif', message: 'Commande prête !');
    }

    public function logout()
    {
        session()->forget(['pos_authenticated', 'kitchen_authenticated']);
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.kitchen-view')->layout('layouts.pos');
    }
}
