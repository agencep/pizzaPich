<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;

class PosPizza extends Component
{
    public $cart = [];
    public $total = 0;
    public $lastChange = null;
    public $sessionTotal = 0;
    public $manualReceived = null;
    public $showNumericPad = false;
    public $menu = [];

    // Settings / Backoffice State
    public $showSettings = false;
    public $initialCash = 0;
    public $allOrdersCount = 0;
    public $productStats = [];
    public $managedProducts = [];
    public $pendingOrders = [];
    public $readyOrders = [];
    public $estimatedTime = 0; // In minutes
    public $settingsTab = 'products'; // 'products', 'stats', 'cash'

    // New Product Form State
    public $newProductName = '';
    public $newProductPrice = 0;
    public $newProductBase = 'tomato';
    public $newProductBaseLabel = 'Base Tomate';
    public $editingProductId = null;
    public $isEditing = false;

    // Split Pizza State
    public $isSplitting = false;
    public $splitStep = 0; 
    public $firstHalf = null;

    public function mount()
    {
        $this->loadMenu();
        $this->updateSessionTotal();
        $this->updatePendingOrders();
        $this->initialCash = (float) Setting::get('initial_cash', 0);
    }

    public function updatePendingOrders()
    {
        $this->pendingOrders = Order::where('status', 'pending')->orderBy('created_at', 'asc')->get()->toArray();
        $this->readyOrders = Order::where('status', 'ready')->orderBy('created_at', 'asc')->get()->toArray();
        $this->estimatedTime = (count($this->pendingOrders) * 5) + 5;
    }

    public function markAsDelivered($id)
    {
        $order = Order::find($id);
        if ($order) {
            $order->status = 'delivered';
            $order->save();
        }
        $this->updatePendingOrders();
        $this->dispatch('notif', message: 'Commande livrée !');
    }

    public function loadMenu()
    {
        $this->menu = Product::where('is_active', true)->get()->toArray();
    }

    public function openSettings()
    {
        $this->showSettings = true;
        $this->managedProducts = Product::all()->toArray();
        $this->loadStats();
    }

    public function loadStats()
    {
        $today = Carbon::today();
        $orders = Order::whereDate('created_at', $today)->get();
        $this->allOrdersCount = $orders->count();
        $this->sessionTotal = $orders->sum('total');
        
        $stats = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $name = $item['name'];
                if (!isset($stats[$name])) {
                    $stats[$name] = ['qty' => 0, 'total' => 0];
                }
                $stats[$name]['qty'] += $item['quantity'];
                $stats[$name]['total'] += ($item['price'] * $item['quantity']);
            }
        }
        $this->productStats = $stats;
    }

    public function saveInitialCash()
    {
        Setting::set('initial_cash', $this->initialCash);
        $this->dispatch('notif', message: 'Fond de caisse enregistré');
    }

    public function editProduct($id)
    {
        $p = Product::find($id);
        $this->editingProductId = $p->id;
        $this->newProductName = $p->name;
        $this->newProductPrice = $p->price;
        $this->newProductBase = $p->base;
        $this->isEditing = true;
        // In Blade we will use @click="openAdd=true" or just ensure it is visible
        $this->dispatch('open-add-form');
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->editingProductId = null;
        $this->newProductName = '';
        $this->newProductPrice = 0;
    }

    public function saveProduct()
    {
        $labels = [
            'tomato' => 'Base Tomate',
            'cream' => 'Base Crème',
            'drink' => 'Boissons',
            'extra' => 'Suppléments',
        ];

        $data = [
            'name' => $this->newProductName,
            'price' => $this->newProductPrice,
            'base' => $this->newProductBase,
            'baseLabel' => $labels[$this->newProductBase] ?? 'Autre',
            'icon' => $this->newProductBase === 'drink' ? '🥤' : ($this->newProductBase === 'extra' ? '✨' : '🍕'),
            'is_active' => true,
        ];

        if ($this->isEditing) {
            Product::find($this->editingProductId)->update($data);
            $this->isEditing = false;
            $this->editingProductId = null;
        } else {
            Product::create($data);
        }

        $this->newProductName = '';
        $this->newProductPrice = 0;
        $this->openSettings(); // Refresh list
        $this->loadMenu(); // Refresh POS
    }

    public function toggleProduct($id)
    {
        $p = Product::find($id);
        $p->is_active = !$p->is_active;
        $p->save();
        $this->openSettings(); // refresh list
        $this->loadMenu(); // refresh POS
    }

    public function resetSales()
    {
        Order::truncate();
        $this->updateSessionTotal();
        $this->updatePendingOrders();
        $this->loadStats();
        $this->dispatch('notif', message: 'Ventes réinitialisées');
    }

    public function startSplit()
    {
        $this->isSplitting = true;
        $this->splitStep = 1;
        $this->firstHalf = null;
    }

    public function cancelSplit()
    {
        $this->isSplitting = false;
        $this->splitStep = 0;
        $this->firstHalf = null;
    }

    public function selectHalf($index)
    {
        $item = $this->menu[$index];
        
        if ($this->splitStep == 1) {
            $this->firstHalf = $item;
            $this->splitStep = 2;
        } else if ($this->splitStep == 2) {
            $secondHalf = $item;
            
            // Create Split Pizza
            $price = max($this->firstHalf['price'], $secondHalf['price']);
            $name = "1/2 " . $this->firstHalf['name'] . " | 1/2 " . $secondHalf['name'];

            $cartItem = [
                'name' => $name,
                'price' => $price,
                'baseLabel' => '🍕 Moitié/Moitié',
                'icon' => '🌓',
                'quantity' => 1,
            ];

            if (isset($this->cart[$name])) {
                $this->cart[$name]['quantity']++;
            } else {
                $this->cart[$name] = $cartItem;
            }

            $this->calculateTotal();
            $this->isSplitting = false;
            $this->splitStep = 0;
            $this->dispatch('item-added');
        }
    }

    public function updateSessionTotal()
    {
        $this->sessionTotal = Order::whereDate('created_at', Carbon::today())->sum('total');
    }

    public function addToCart($itemIndex)
    {
        $item = $this->menu[$itemIndex];
        $name = $item['name'];

        if (isset($this->cart[$name])) {
            $this->cart[$name]['quantity']++;
        } else {
            $this->cart[$name] = [
                'name' => $item['name'],
                'price' => $item['price'],
                'baseLabel' => $item['baseLabel'],
                'icon' => $item['icon'],
                'quantity' => 1,
            ];
        }

        $this->calculateTotal();
        $this->lastChange = null;
        $this->dispatch('item-added');
    }

    public function removeFromCart($name)
    {
        if (isset($this->cart[$name])) {
            if ($this->cart[$name]['quantity'] > 1) {
                $this->cart[$name]['quantity']--;
            } else {
                unset($this->cart[$name]);
            }
        }
        $this->calculateTotal();
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->total = 0;
        $this->lastChange = null;
    }

    public function calculateTotal()
    {
        $this->total = array_reduce($this->cart, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
    }

    public function handleCash($received)
    {
        if ($this->total == 0) return;
        if ($received < $this->total) return;

        $received = (float) $received;

        // Save to Database
        Order::create([
            'total' => $this->total,
            'received' => $received,
            'change' => $received - $this->total,
            'items' => $this->cart,
            'status' => 'pending',
        ]);

        $change = $received - $this->total;
        $this->lastChange = ($change > 0.001) ? $change : null;
        
        // Reset Cart
        $this->cart = [];
        $this->total = 0;

        $this->updateSessionTotal();
        $this->updatePendingOrders();
        $this->dispatch('payment-completed');
    }

    public function resetChange()
    {
        $this->lastChange = null;
    }

    public function render()
    {
        return view('livewire.pos-pizza')->layout('layouts.pos');
    }
}
