<div wire:poll.3s="updatePendingOrders" 
     x-data="{ showNewOrderAlert: false }"
     @new-order-alert.window="showNewOrderAlert = true; setTimeout(() => showNewOrderAlert = false, 4000)"
     class="h-screen bg-[#050505] text-white flex flex-col overflow-hidden select-none touch-pan-x relative">
    
    <!-- New Order Flash Overlay -->
    <div x-show="showNewOrderAlert" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-100 pointer-events-none flex items-center justify-center bg-orange-600/20 backdrop-blur-sm border-16 border-orange-500">
         
         <div class="bg-[#050505] border-4 border-orange-500 rounded-[3rem] px-16 py-10 shadow-[0_0_150px_rgba(249,115,22,1)] animate-bounce text-center">
             <h2 class="text-orange-500 text-6xl md:text-8xl font-black uppercase tracking-tighter italic drop-shadow-xl flex items-center gap-4">
                <span>🔥</span> NOUVELLE COMMANDE <span>🔥</span>
             </h2>
         </div>
    </div>
    
    <!-- Header Mobile -->
    <header class="h-16 shrink-0 flex items-center justify-between px-6 border-b border-white/5">
        <div class="flex items-center gap-3">
            <button onclick="confirm('Se déconnecter de la cuisine ?') && @this.logout()" 
                    class="h-10 w-10 flex items-center justify-center rounded-full bg-red-600/10 border border-red-500/20 text-red-500 hover:bg-red-600 hover:text-white transition-all active:scale-95"
                    title="Déconnexion">
                <span class="text-xs font-black">⏻</span>
            </button>
            <h1 class="text-xl font-black uppercase tracking-tighter italic leading-none">Cuisine</h1>

            @if(session('pos_authenticated'))
                <a href="{{ route('home') }}" class="ml-2 flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 transition-all group">
                    <span class="text-[0.6rem] font-black uppercase tracking-widest opacity-40 group-hover:opacity-100">Caisse</span>
                    <span class="text-xs">🖥️</span>
                </a>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-white/5 px-3 py-1.5 rounded-full border border-white/5">
                <span class="text-[0.6rem] font-black text-white/40 uppercase tracking-widest leading-none">Attente:</span>
                <span class="text-xs font-black text-orange-500 tabular-nums leading-none">{{ $estimatedTime }} min</span>
            </div>
            <div class="bg-orange-600 px-4 py-1.5 rounded-full shadow-lg shadow-orange-600/20 flex items-center gap-2">
                <span class="text-xs font-black tabular-nums leading-none">{{ count($pendingOrders) }}</span>
                <span class="text-[0.6rem] font-black uppercase tracking-widest leading-none">Com.</span>
            </div>
        </div>
    </header>

    <!-- Orders Horizontal Slide -->
    <div class="grow overflow-x-auto flex gap-6 p-6 no-scrollbar snap-x snap-mandatory h-full pb-10">
        @forelse($pendingOrders as $order)
            <div class="w-[85vw] md:w-[400px] h-full shrink-0 flex flex-col bg-zinc-900 rounded-[2.5rem] border border-white/10 shadow-2xl overflow-hidden snap-center animate-in slide-in-from-right-10 duration-300">
                <!-- Card Header -->
                <div class="py-4 px-6 bg-orange-600 flex justify-between items-center text-black shrink-0 shadow-lg">
                    <div>
                        <p class="text-[0.6rem] font-bold uppercase opacity-60 text-black/70">Commande #{{ $order['id'] }}</p>
                        <p class="text-xs font-black tabular-nums leading-none mt-1">
                            {{ \Carbon\Carbon::parse($order['created_at'])->format('H:i') }} 
                        </p>
                    </div>

                    <button wire:click="markAsReady({{ $order['id'] }})"
                            class="bg-green-600 text-white px-10 py-5 rounded-full font-black text-lg uppercase tracking-tighter active:scale-95 transition-all shadow-xl border border-black/10">
                        Prêt(e) ✅
                    </button>
                </div>

                <!-- Articles -->
                <div class="grow overflow-y-auto p-8 space-y-6 no-scrollbar">
                    @foreach($order['items'] as $item)
                        <div class="flex items-start gap-4">
                            <div class="shrink-0 w-10 h-10 rounded-full bg-orange-500/10 flex items-center justify-center text-lg font-black text-orange-500 border border-orange-500/20">
                                {{ $item['quantity'] }}
                            </div>
                            <div class="grow">
                                <p class="text-2xl font-black uppercase leading-tight tracking-tight">{{ $item['name'] }}</p>
                                <p class="text-[0.7rem] font-bold text-white/20 uppercase tracking-widest mt-1 italic">{{ $item['baseLabel'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        @empty
            <div class="w-full h-full flex flex-col items-center justify-center opacity-10">
                <span class="text-9xl mb-6">💤</span>
                <p class="text-xl font-black uppercase tracking-[0.3em] text-center leading-tight italic">Aucune commande <br> en préparation</p>
            </div>
        @endforelse
    </div>

    <!-- Notification system -->
    <div x-data="{ show: false, message: '' }" 
         @notif.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 3000)"
         x-show="show" x-cloak
         class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50 px-8 py-4 bg-white text-black rounded-full font-black text-xs uppercase tracking-widest shadow-2xl animate-in fade-in slide-in-from-bottom-5">
        <span x-text="message"></span>
    </div>

</div>
