<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use Carbon\Carbon;

class KitchenView extends Component
{
    public $pendingOrders = [];

    public function mount()
    {
        $this->updatePendingOrders();
    }

    public function updatePendingOrders()
    {
        $this->pendingOrders = Order::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
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

    public function render()
    {
        return view('livewire.kitchen-view')->layout('layouts.pos');
    }
}
