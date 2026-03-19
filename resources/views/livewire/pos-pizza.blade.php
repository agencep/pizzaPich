<div x-data="posView()" 
     wire:poll.10s="updatePendingOrders"
     @item-added.window="beep(600, 0.05); vibrate(20)"
     @payment-completed.window="successSound(); vibrate([50, 30, 50])"
     @open-add-form.window="openAdd = true"
     class="h-screen w-full flex flex-col bg-[#050505] text-white overflow-hidden select-none touch-none">

    <!-- COMPACT HEADER -->
    <header class="glass h-14 shrink-0 flex items-center justify-between px-4 z-20 border-b border-white/10">
        <div class="flex items-center gap-3">
            <h1 class="text-lg font-black tracking-tighter uppercase italic">PIZZA <span class="text-red-600">PICH'</span></h1>
            
            <!-- Interface Switch -->
            <a href="{{ route('kitchen') }}" class="ml-2 flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 transition-all group">
                <span class="text-[0.6rem] font-black uppercase tracking-widest opacity-40 group-hover:opacity-100">Cuisine</span>
                <span class="text-xs">👨‍🍳</span>
            </a>

            <!-- ETD Indicator -->
            <div class="ml-4 flex items-center gap-2 bg-white/5 px-3 py-1 rounded-full border border-white/5">
                <span class="text-[0.5rem] font-black text-white/40 uppercase tracking-widest">Attente Estimée:</span>
                <span class="text-xs font-black text-orange-500 tabular-nums">{{ $estimatedTime }} min</span>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Settings Button -->
            <button wire:click="openSettings" 
                    class="h-8 w-8 flex items-center justify-center rounded-full bg-white/5 border border-white/10 opacity-60 hover:opacity-100 hover:bg-white/10 transition-all active:scale-95"
                    title="Paramètres">
                <span class="text-sm">⚙️</span>
            </button>

            <!-- Logout Button -->
            <button onclick="confirm('Se déconnecter de la caisse ?') && @this.logout()" 
                    class="h-8 w-8 flex items-center justify-center rounded-full bg-red-600/10 border border-red-500/20 text-red-500 hover:bg-red-600 hover:text-white transition-all active:scale-95"
                    title="Déconnexion">
                <span class="text-xs font-black">⏻</span>
            </button>
            <!-- Distribution Button -->
            @if(count($readyOrders) > 0)
                <button @click="showDistribution = true" 
                        class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-green-600 border border-green-500/30 text-white active:scale-95 transition-all shadow-lg shadow-green-600/20">
                    <span class="text-[0.6rem] font-black uppercase tracking-widest">🎁 À Livrer</span>
                    <span class="bg-black/20 w-5 h-5 flex items-center justify-center rounded-full text-[0.6rem] font-black">{{ count($readyOrders) }}</span>
                </button>
            @endif

            <!-- Kitchen Monitor Button -->
            <button @click="showKitchen = true" 
                    class="relative flex items-center gap-2 px-4 py-1.5 rounded-full bg-orange-600/20 border border-orange-500/30 text-orange-400 active:scale-95 transition-all">
                <span class="text-[0.6rem] font-black uppercase tracking-widest">🍳 Cuisine</span>
                @if(count($pendingOrders) > 0)
                    <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-orange-600 text-[0.5rem] font-black text-white animate-bounce">
                        {{ count($pendingOrders) }}
                    </span>
                @endif
            </button>

            @if($isSplitting)
                <div class="flex items-center gap-4 bg-purple-600/20 border border-purple-500/30 px-4 py-1.5 rounded-full animate-pulse">
                    <span class="text-[0.6rem] font-black uppercase tracking-widest text-purple-400">
                        Mode Split: @if($splitStep == 1) 1ère moitié @else 2ème moitié @endif
                    </span>
                    <button wire:click="cancelSplit" class="text-xs font-black hover:text-white text-purple-400">Annuler</button>
                </div>
            @endif
        </div>

        <div class="text-right">
            <span class="text-[0.6rem] font-bold text-white/40 uppercase tracking-widest mr-2">Total Soirée:</span>
            <span class="text-sm font-black text-red-600 tabular-nums">{{ number_format($sessionTotal, 3) }} TND</span>
        </div>
    </header>

    <!-- CONTENT AREA -->
    <div class="flex-grow flex flex-col md:flex-row h-0 overflow-hidden">
        
        <!-- SECTION MENU -->
        <div class="flex-[1.5] flex flex-col overflow-hidden bg-black/20">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-3 gap-1.5 p-2 overflow-y-auto no-scrollbar">
                @if(!$isSplitting)
                    <button wire:click="startSplit"
                            class="relative flex flex-col justify-center items-center py-5 px-1 rounded-2xl border border-purple-500/40 bg-purple-600/20 active:scale-95 transition-all text-center overflow-hidden">
                        <span class="text-2xl mb-1">🌗</span>
                        <span class="text-[0.65rem] font-black uppercase tracking-tight leading-tight">Moitié / Moitié</span>
                    </button>
                @endif

                @foreach($menu as $index => $item)
                    <button wire:click="{{ $isSplitting ? 'selectHalf('.$index.')' : 'addToCart('.$index.')' }}"
                            class="relative flex flex-col justify-center items-center py-5 px-1 rounded-2xl border active:scale-95 transition-all text-center group overflow-hidden border-white/5"
                            style="background-color: {{ $item['base'] === 'tomato' ? 'rgba(185, 28, 28, 0.15)' : 'rgba(217, 119, 6, 0.15)' }};">
                        
                        @if($isSplitting && $firstHalf && $firstHalf['name'] === $item['name'])
                            <div class="absolute inset-0 bg-purple-600/40 flex items-center justify-center z-10">✅</div>
                        @endif

                        <span class="text-xs font-black uppercase tracking-tight leading-tight">{{ $item['name'] }}</span>
                        <div class="mt-1 px-2 py-0.5 rounded-full bg-black/40 text-[0.6rem] font-black border border-white/10">{{ number_format($item['price'], 3) }}</div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- SECTION PANIER -->
        <div class="flex-1 flex flex-col border-t md:border-t-0 md:border-l border-white/10 bg-[#0a0a0a] overflow-hidden">
            <div class="flex-grow flex flex-col overflow-hidden">
                <div class="h-10 shrink-0 flex items-center justify-between px-5 bg-white/5 border-b border-white/5">
                    <span class="text-[0.6rem] font-black uppercase tracking-[0.3em] text-white/40">COMMANDE ATUELLE</span>
                    <button wire:click="clearCart" class="text-[0.6rem] font-black text-red-500 uppercase tracking-widest px-3 py-1 bg-red-600/10 rounded-full">Vider</button>
                </div>
                
                <div class="grow overflow-y-auto no-scrollbar p-3 space-y-1.5">
                    @forelse($cart as $name => $item)
                        <div wire:click="removeFromCart('{{ $name }}')" 
                             class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/5 border border-white/5 active:bg-red-600/10">
                            <span class="text-xs font-black uppercase tracking-tight">
                                <span class="text-red-500 mr-1">{{ $item['quantity'] }}x</span>
                                {{ $name }}
                            </span>
                            <span class="text-[0.7rem] font-black text-red-500">{{ number_format($item['price'] * $item['quantity'], 3) }}</span>
                        </div>
                    @empty
                        <div class="h-full flex items-center justify-center opacity-10 italic text-[0.5rem] font-black tracking-widest">Panier Vide</div>
                    @endforelse
                </div>
            </div>

            <!-- Total Bar -->
            <div class="p-4 bg-black/60 border-t border-white/10 text-center shrink-0">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-[0.6rem] font-black uppercase tracking-[0.4em] text-white/30 italic">À Encaisser</span>
                    <span class="text-5xl font-black tracking-tighter tabular-nums text-red-600">{{ number_format($total, 3) }}</span>
                </div>

                <div class="grid grid-cols-3 gap-1.5">
                    <button wire:click="handleCash(5)" class="glass py-2.5 rounded-xl font-black text-lg active:scale-95 transition-transform italic bg-white/5">5</button>
                    <button wire:click="handleCash(10)" class="glass py-2.5 rounded-xl font-black text-lg active:scale-95 transition-transform italic bg-white/5">10</button>
                    <button wire:click="handleCash(20)" class="glass py-2.5 rounded-xl font-black text-lg active:scale-95 transition-transform italic bg-white/5">20</button>
                    <button wire:click="handleCash(50)" class="glass py-2.5 rounded-xl font-black text-lg active:scale-95 transition-transform italic bg-white/5">50</button>
                    <button wire:click="handleCash({{ $total }})" class="bg-red-600/20 text-red-500 border border-red-500/30 py-2.5 rounded-xl font-black text-[0.6rem] uppercase tracking-widest active:scale-95">Exact</button>
                    <button @click="showNumPad = true" class="bg-blue-600/20 text-blue-400 border border-blue-400/30 py-2.5 rounded-xl font-black text-[0.6rem] uppercase tracking-widest active:scale-95">Autre</button>
                </div>
            </div>
        </div>
    </div>

    <!-- KITCHEN MONITOR MODAL -->
    <div x-show="showKitchen" x-cloak 
         class="fixed inset-0 z-50 bg-[#050505] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <header class="h-16 shrink-0 flex items-center justify-between px-6 border-b border-white/10 bg-orange-600/10">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🍳</span>
                <h2 class="text-xl font-black uppercase tracking-tighter italic">En préparation (Cuisine)</h2>
            </div>
            <button @click="showKitchen = false" class="px-8 py-3 bg-white/5 rounded-full text-xs font-black uppercase tracking-widest border border-white/10 active:scale-95">Fermer</button>
        </header>
        <div class="grow overflow-x-auto p-6 flex gap-6 no-scrollbar h-full">
            @forelse($pendingOrders as $order)
                <div class="w-[320px] h-full shrink-0 flex flex-col bg-zinc-900 rounded-[2.5rem] border border-white/10 shadow-2xl overflow-hidden">
                    <div class="p-6 bg-orange-600 flex justify-between items-center text-black">
                        <div>
                            <p class="text-[0.6rem] font-bold uppercase opacity-50 text-black/60">Commande #{{ $order['id'] }}</p>
                            <p class="text-xs font-black tabular-nums">{{ \Carbon\Carbon::parse($order['created_at'])->format('H:i') }}</p>
                        </div>
                    </div>
                    <div class="grow overflow-y-auto space-y-4 no-scrollbar p-6">
                        @foreach($order['items'] as $item)
                        <div class="flex items-start justify-between gap-4">
                            <span class="text-sm font-black uppercase leading-tight"><span class="text-orange-500 mr-2">{{ $item['quantity'] }}x</span>{{ $item['name'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="w-full h-full flex flex-col items-center justify-center opacity-10 italic uppercase font-black tracking-widest text-xl">Cuisine Vide</div>
            @endforelse
        </div>
    </div>

    <!-- DISTRIBUTION MODAL (À Livrer) -->
    <div x-show="showDistribution" x-cloak 
         class="fixed inset-0 z-50 bg-[#050505] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <header class="h-16 shrink-0 flex items-center justify-between px-6 border-b border-white/10 bg-green-600/10">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🎁</span>
                <h2 class="text-xl font-black uppercase tracking-tighter italic text-green-500">Prêtes à distribuer</h2>
            </div>
            <button @click="showDistribution = false" class="px-8 py-3 bg-white/5 rounded-full text-xs font-black uppercase tracking-widest border border-white/10 active:scale-95">Fermer</button>
        </header>

        <div class="grow overflow-x-auto p-6 flex gap-6 no-scrollbar h-full">
            @foreach($readyOrders as $order)
                <div class="w-[320px] h-full shrink-0 flex flex-col bg-zinc-900 rounded-[2.5rem] border border-white/10 shadow-2xl overflow-hidden border-green-500/30">
                    <div class="p-6 bg-green-600 flex justify-between items-center text-black">
                        <div>
                            <p class="text-[0.6rem] font-bold uppercase opacity-60 text-black/70">Prête #{{ $order['id'] }}</p>
                            <p class="text-xs font-black tabular-nums">{{ \Carbon\Carbon::parse($order['created_at'])->format('H:i') }}</p>
                        </div>
                        <span class="text-2xl animate-bounce">🎁</span>
                    </div>

                    <div class="grow overflow-y-auto space-y-4 no-scrollbar p-6">
                        @foreach($order['items'] as $item)
                        <div class="flex items-start justify-between gap-4">
                            <span class="text-sm font-black uppercase leading-tight text-white/90">
                                <span class="text-green-500 mr-2">{{ $item['quantity'] }}x</span>
                                {{ $item['name'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>

                    <div class="p-6 border-t border-white/5 bg-black/20 text-center shrink-0">
                        <button wire:click="markAsDelivered({{ $order['id'] }})"
                                class="w-full bg-green-500 py-6 rounded-3xl font-black text-xl uppercase tracking-tighter shadow-xl shadow-green-600/20 active:scale-95 transition-all leading-none text-black">
                            Livrée ✅
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- SETTINGS MODAL (Backoffice) -->
    @if($showSettings)
    <div class="fixed inset-0 z-[100] bg-black flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom-10 duration-300">
        <header class="h-16 shrink-0 flex items-center justify-between px-6 border-b border-white/10 bg-zinc-900">
            <div class="flex items-center gap-6">
                <h2 class="text-lg font-black uppercase tracking-tighter">Backoffice</h2>
                <!-- TABS -->
                <nav class="flex gap-1 bg-black/40 p-1 rounded-full border border-white/5">
                    <button wire:click="$set('settingsTab', 'products')" class="px-6 py-1.5 rounded-full text-[0.6rem] font-black uppercase transition-all {{ $settingsTab === 'products' ? 'bg-red-600 text-white' : 'text-white/40 hover:text-white' }}">Articles</button>
                    <button wire:click="$set('settingsTab', 'stats')" class="px-6 py-1.5 rounded-full text-[0.6rem] font-black uppercase transition-all {{ $settingsTab === 'stats' ? 'bg-red-600 text-white' : 'text-white/40 hover:text-white' }}">Ventes</button>
                    <button wire:click="$set('settingsTab', 'cash')" class="px-6 py-1.5 rounded-full text-[0.6rem] font-black uppercase transition-all {{ $settingsTab === 'cash' ? 'bg-red-600 text-white' : 'text-white/40 hover:text-white' }}">Caisse</button>
                </nav>
            </div>
            <button wire:click="$set('showSettings', false)" class="px-6 py-2 bg-white/5 rounded-full text-xs font-black uppercase">Fermer</button>
        </header>

        <div class="flex-grow overflow-y-auto p-8 space-y-10 no-scrollbar pb-20">
            
            @if($settingsTab === 'products')
                <!-- ARTICLE GESTION -->
                <section x-data="{ openAdd: false }" @open-add-form.window="openAdd = true">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-[0.7rem] font-black text-white/40 uppercase tracking-[0.5em]">Gestion du catalogue</h3>
                        <button @click="openAdd = !openAdd; if(!openAdd) $wire.cancelEdit()" 
                                class="px-6 py-2.5 bg-red-600 rounded-full text-[0.6rem] font-black uppercase shadow-lg shadow-red-600/20 active:scale-95 transition-all">
                            <span x-text="openAdd ? 'Fermer le formulaire' : '+ Nouveau Produit'"></span>
                        </button>
                    </div>

                    <!-- FORM -->
                    <div x-show="openAdd" class="glass p-8 rounded-[2rem] mb-10 border-white/5" :class="$wire.isEditing ? 'bg-blue-600/10' : 'bg-red-600/10'">
                        <h4 class="text-[0.6rem] font-black uppercase mb-6 opacity-50" x-text="$wire.isEditing ? 'Modifier l\'article' : 'Informations du nouveau produit'"></h4>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                            <div class="sm:col-span-2">
                                <label class="block text-[0.5rem] font-black uppercase text-white/30 mb-2">Nom de l'article</label>
                                <input wire:model="newProductName" type="text" class="w-full bg-black/40 border-2 border-white/5 rounded-2xl px-5 py-4 text-sm font-bold focus:border-red-600 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-[0.5rem] font-black uppercase text-white/30 mb-2">Prix (TND)</label>
                                <input wire:model="newProductPrice" type="number" step="0.001" class="w-full bg-black/40 border-2 border-white/5 rounded-2xl px-5 py-4 text-sm font-bold focus:border-red-600 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-[0.5rem] font-black uppercase text-white/30 mb-2">Catégorie / Base</label>
                                <select wire:model="newProductBase" class="w-full bg-black/40 border-2 border-white/5 rounded-2xl px-5 py-4 text-sm font-bold focus:border-red-600 outline-none transition-all">
                                    <option value="tomato">Piz. Tomate</option>
                                    <option value="cream">Piz. Crème</option>
                                    <option value="drink">Boisson</option>
                                    <option value="extra">Supplément</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-3 mt-8">
                            <button wire:click="saveProduct" @click="openAdd = false" 
                                    class="flex-grow py-5 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl active:scale-95 transition-all"
                                    :class="$wire.isEditing ? 'bg-blue-600 shadow-blue-600/20' : 'bg-red-600 shadow-red-600/20'"
                                    x-text="$wire.isEditing ? 'Sauvegarder les modifications' : 'Ajouter au menu'"></button>
                            @if($isEditing)
                                <button wire:click="cancelEdit" @click="openAdd = false" class="px-10 bg-zinc-800 py-5 rounded-2xl font-black uppercase text-xs tracking-widest active:scale-95">Annuler</button>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($managedProducts as $p)
                            <div class="glass p-6 rounded-[2rem] flex flex-col gap-4 {{ !$p['is_active'] ? 'opacity-30 grayscale' : '' }} border-white/5 hover:border-white/10 transition-all group">
                                <div class="flex items-start justify-between">
                                    <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                        {{ $p['icon'] }}
                                    </div>
                                    <button wire:click="toggleProduct({{ $p['id'] }})" class="p-2 rounded-xl transition-all {{ $p['is_active'] ? 'text-green-500 bg-green-500/10' : 'text-white/20 bg-white/5' }}">
                                        <div class="w-2 h-2 rounded-full bg-current shadow-[0_0_8px_currentColor]"></div>
                                    </button>
                                </div>
                                <div>
                                    <p class="text-sm font-black uppercase tracking-tight">{{ $p['name'] }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs font-black text-red-500">{{ number_format($p['price'], 3) }}</span>
                                        <span class="text-[0.5rem] font-bold text-white/20 uppercase tracking-widest">{{ $p['baseLabel'] }}</span>
                                    </div>
                                </div>
                                <div class="flex gap-2 pt-2">
                                    <button wire:click="editProduct({{ $p['id'] }})" class="flex-grow py-3 bg-white/5 hover:bg-white/10 rounded-xl text-[0.6rem] font-black uppercase tracking-widest transition-all">Modifier</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($settingsTab === 'stats')
                <!-- SALES STATS -->
                <section class="animate-in fade-in slide-in-from-bottom-5">
                    <div class="flex justify-between items-end mb-8">
                        <div>
                            <h3 class="text-[0.7rem] font-black text-white/40 uppercase tracking-[0.5em] mb-2">Rapport des ventes</h3>
                            <p class="text-4xl font-black tracking-tighter tabular-nums">{{ $allOrdersCount }} <span class="text-sm uppercase text-white/20 tracking-normal ml-2">Commandes aujourd'hui</span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[0.6rem] font-black text-white/40 uppercase tracking-widest mb-1">Chiffre d'affaires</p>
                            <p class="text-4xl font-black tracking-tighter tabular-nums text-red-600">{{ number_format($sessionTotal, 3) }} <span class="text-sm tracking-normal ml-1">TND</span></p>
                        </div>
                    </div>

                    <div class="glass rounded-[2.5rem] overflow-hidden border-white/5">
                        <table class="w-full text-left">
                            <thead class="bg-white/5 text-[0.6rem] font-black uppercase text-white/40 border-b border-white/5">
                                <tr>
                                    <th class="px-8 py-6 uppercase tracking-[0.2em]">Article</th>
                                    <th class="px-8 py-6 text-center uppercase tracking-[0.2em]">Quantité</th>
                                    <th class="px-8 py-6 text-right uppercase tracking-[0.2em]">Total (TND)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach($productStats as $name => $data)
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="px-8 py-5 text-sm font-black uppercase tracking-tight">{{ $name }}</td>
                                    <td class="px-8 py-5 text-center font-black tabular-nums scale-110">
                                        <span class="inline-block px-3 py-1 bg-white/5 rounded-lg border border-white/5">{{ $data['qty'] }}</span>
                                    </td>
                                    <td class="px-8 py-5 text-right font-black tabular-nums text-red-500 text-lg">{{ number_format($data['total'], 3) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-black/40 border-t-2 border-red-600/20">
                                <tr>
                                    <td class="px-8 py-8 font-black uppercase text-sm italic">TOTAL GÉNÉRAL</td>
                                    <td class="px-8 py-8"></td>
                                    <td class="px-8 py-8 text-right font-black text-3xl tabular-nums tracking-tighter">{{ number_format($sessionTotal, 3) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            @endif

            @if($settingsTab === 'cash')
                <!-- CASH MANAGEMENT -->
                <section class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="glass p-10 rounded-[3rem] border border-white/5 flex flex-col justify-between">
                            <div>
                                <h3 class="text-[0.7rem] font-black text-white/40 uppercase tracking-[0.5em] mb-8">Fond de Caisse Initial</h3>
                                <p class="text-[0.6rem] font-bold text-white/20 mb-4 uppercase leading-relaxed">Saisissez le montant présent dans le tiroir au début de la session.</p>
                            </div>
                            <div class="mt-auto">
                                <div class="relative mb-6">
                                    <input wire:model.live="initialCash" type="number" step="0.001"
                                           class="w-full bg-black border-2 border-white/10 rounded-[1.5rem] py-6 px-6 text-4xl font-black text-center focus:border-red-600 outline-none transition-all shadow-inner">
                                    <span class="absolute right-6 top-1/2 -translate-y-1/2 text-xl font-black opacity-10 uppercase tracking-widest pointer-events-none">TND</span>
                                </div>
                                <button wire:click="saveInitialCash" class="w-full bg-white text-black py-5 rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl active:scale-95 transition-all">Enregistrer le fond</button>
                            </div>
                        </div>

                        <div class="bg-red-600 p-10 rounded-[3.5rem] shadow-2xl shadow-red-600/30 text-center flex flex-col justify-center relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-125 transition-transform duration-700">
                                <span class="text-[10rem] font-black italic">TND</span>
                            </div>
                            <div class="relative z-10">
                                <h3 class="text-[0.7rem] font-black text-white/30 uppercase tracking-[0.5em] mb-6">Total théorique en caisse</h3>
                                <p class="text-7xl font-black tracking-tighter tabular-nums drop-shadow-2xl">{{ number_format((float)$initialCash + (float)$sessionTotal, 3) }}</p>
                                <div class="mt-8 flex flex-col gap-2">
                                    <div class="flex justify-between text-[0.6rem] font-black uppercase tracking-widest text-white/50 px-4">
                                        <span>Fond:</span>
                                        <span>+ {{ number_format((float)$initialCash, 3) }}</span>
                                    </div>
                                    <div class="flex justify-between text-[0.6rem] font-black uppercase tracking-widest text-white/50 px-4">
                                        <span>Ventes:</span>
                                        <span>+ {{ number_format((float)$sessionTotal, 3) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RESET ACTION -->
                    <div class="p-10 bg-zinc-900/50 rounded-[3rem] border border-red-500/10 text-center">
                        <h3 class="text-lg font-black uppercase tracking-tighter mb-2">Fin de session ?</h3>
                        <p class="text-xs font-bold text-white/30 mb-8 max-w-md mx-auto">Cette action remettra à zéro toutes les ventes, le fond de caisse et videra la file d'attente de la cuisine pour une nouvelle journée.</p>
                        <button onclick="confirm('Voulez-vous vraiment vider toutes les ventes et recommencer une nouvelle session ?') && @this.resetSales()" 
                                class="bg-red-600 text-white px-10 py-5 rounded-full font-black uppercase tracking-tighter text-xs shadow-xl shadow-red-600/20 hover:bg-red-700 hover:shadow-red-600/40 active:scale-95 transition-all">
                            🚀 On commence la soirée ! (Reset Complet)
                        </button>
                    </div>
                </section>
            @endif

        </div>
    </div>
    @endif

    <!-- NUMERIC Pad -->
    <div x-show="showNumPad" x-cloak 
         x-data="{ 
            currentVal: '', 
            add(n) { this.currentVal += n }, 
            back() { this.currentVal = this.currentVal.slice(0, -1) },
            clear() { this.currentVal = '' },
            submit() { 
                if(this.currentVal === '') return;
                $wire.handleCash(parseFloat(this.currentVal));
                this.currentVal = '';
                showNumPad = false;
            }
         }"
         class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/95 backdrop-blur-md">
        
        <div class="w-full max-w-[320px] bg-[#111] rounded-[2.5rem] p-5 border border-white/10 shadow-2xl text-center text-white space-y-3 max-h-[96vh] overflow-y-auto no-scrollbar">
            <h3 class="text-[0.6rem] font-black uppercase tracking-widest text-white/40 italic">Montant reçu</h3>
            
            <!-- Output Display -->
            <div class="bg-black border-2 border-white/10 rounded-2xl py-4 px-4 text-4xl font-black text-center tabular-nums text-red-600 min-h-[72px] flex items-center justify-center relative">
                <span x-text="currentVal"></span>
                <span x-show="currentVal === ''" class="opacity-20 font-black text-3xl">0</span>
                <span class="absolute right-4 text-[0.6rem] opacity-20 font-black">TND</span>
            </div>

            <!-- Keypad Grid -->
            <div class="grid grid-cols-3 gap-2">
                @foreach([1,2,3,4,5,6,7,8,9] as $num)
                    <button @click="add('{{ $num }}')" class="h-14 rounded-2xl bg-white/5 border border-white/5 text-xl font-black active:bg-white/20 transition-all active:scale-95">{{ $num }}</button>
                @endforeach
                <button @click="add('.')" class="h-14 rounded-2xl bg-white/5 border border-white/5 text-xl font-black active:bg-white/20 transition-all active:scale-95">.</button>
                <button @click="add('0')" class="h-14 rounded-2xl bg-white/5 border border-white/5 text-xl font-black active:bg-white/20 transition-all active:scale-95">0</button>
                <button @click="back()" class="h-14 rounded-2xl bg-red-600/10 border border-red-500/20 text-red-500 text-lg font-black active:bg-red-600/20 transition-all active:scale-95">⌫</button>
            </div>

            <div class="flex gap-2 pt-2">
                <button @click="clear()" class="flex-1 py-4 rounded-xl font-black uppercase text-[0.6rem] tracking-widest bg-zinc-800 active:scale-95 transition-all">Vider</button>
                <button @click="submit()" class="flex-[1.5] py-4 rounded-xl font-black uppercase text-[0.6rem] tracking-widest bg-red-600 shadow-lg shadow-red-600/20 active:scale-95 transition-all">Valider</button>
            </div>
            
            <button @click="showNumPad = false; currentVal = ''" class="block w-full text-[0.5rem] font-bold text-white/20 uppercase tracking-[0.2em] pt-1">Fermer</button>
        </div>
    </div>

    @if($lastChange !== null)
        <div @click="$wire.resetChange(); beep(400, 0.05)" class="fixed inset-0 z-60 bg-green-500 flex flex-col items-center justify-center text-center p-8 active:scale-95 transition-transform duration-300">
            <h3 class="text-[12rem] font-black tracking-tighter text-black tabular-nums drop-shadow-xl">{{ number_format($lastChange, 3) }}</h3>
            <p class="text-[1.5rem] font-black text-black/40 mt-2 uppercase tracking-widest">TND</p>
            <p class="mt-8 text-[0.6rem] font-black uppercase tracking-widest bg-black/10 px-8 py-4 rounded-full text-black/60 italic">Tap pour continuer</p>
        </div>
    @endif

    <script>
        function posView() {
            return {
                showNumPad: false,
                showKitchen: false,
                showDistribution: false,
                audioCtx: null,
                initAudio() { if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)(); },
                beep(freq = 440, duration = 0.1, type = 'sine', volume = 0.04) {
                    this.initAudio(); if (!this.audioCtx) return;
                    const osc = this.audioCtx.createOscillator(); const gain = this.audioCtx.createGain();
                    osc.type = type; osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime);
                    gain.gain.setValueAtTime(volume, this.audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, this.audioCtx.currentTime + duration);
                    osc.connect(gain); gain.connect(this.audioCtx.destination);
                    osc.start(); osc.stop(this.audioCtx.currentTime + duration);
                },
                successSound() { setTimeout(() => this.beep(880, 0.1), 0); setTimeout(() => this.beep(1108, 0.1), 50); setTimeout(() => this.beep(1320, 0.2), 100); },
                vibrate(p) { if (navigator.vibrate) navigator.vibrate(p); }
            }
        }
    </script>
</div>
