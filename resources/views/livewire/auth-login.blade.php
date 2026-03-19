<div class="h-screen w-full flex bg-[#050505] text-white overflow-hidden p-4 md:p-8">
    
    <!-- LEFT SIDE: Branding & Code Status -->
    <div class="flex-1 flex flex-col justify-center items-center text-center space-y-8 border-r border-white/5">
        <header class="space-y-2">
            <h1 class="text-5xl font-black italic tracking-tighter uppercase">PIZZA <span class="text-red-600">PICH'</span></h1>
            <p class="text-[0.65rem] font-black uppercase tracking-[0.5em] text-white/30 italic">Session Protégée</p>
        </header>

        <!-- OTP Display -->
        <div class="relative py-8">
            <div class="flex gap-4">
                @for($i = 0; $i < 8; $i++)
                    <div class="w-3.5 h-3.5 rounded-full border-2 border-white/10 transition-all {{ strlen($otp) > $i ? 'bg-red-600 border-red-600 shadow-[0_0_20px_rgba(220,38,38,0.7)] scale-110' : '' }}"></div>
                @endfor
            </div>
            
            @if($error)
                <p class="absolute -bottom-4 left-0 right-0 text-center text-red-500 text-[0.7rem] font-black uppercase tracking-widest animate-pulse">{{ $error }}</p>
            @endif
        </div>

        <p class="text-[0.6rem] font-black uppercase tracking-[0.3em] text-white/10">Saisir le code d'accès à 8 chiffres</p>
    </div>

    <!-- RIGHT SIDE: Numeric Keypad -->
    <div class="flex-1 flex items-center justify-center">
        <div class="grid grid-cols-3 gap-2.5 w-full max-w-[320px]">
            @foreach([1,2,3,4,5,6,7,8,9] as $num)
                <button wire:click="$set('otp', '{{ $otp.$num }}')" 
                        class="h-14 rounded-2xl bg-white/5 border border-white/5 text-xl font-black active:bg-white/20 transition-all active:scale-95">
                    {{ $num }}
                </button>
            @endforeach
            <button wire:click="$set('otp', '')" class="h-14 rounded-2xl bg-zinc-900 border border-white/5 text-[0.6rem] font-black uppercase opacity-40">Effacer</button>
            <button wire:click="$set('otp', '{{ $otp }}0')" 
                    class="h-14 rounded-2xl bg-white/5 border border-white/5 text-xl font-black active:bg-white/20 transition-all active:scale-95">0</button>
            <button wire:click="login" class="h-14 rounded-2xl bg-red-600 border border-red-500/20 text-white text-lg font-black active:bg-red-700 transition-all active:scale-95 uppercase tracking-tighter italic">OK</button>
        </div>
    </div>

    <!-- Automatic validation -->
    @if(strlen($otp) >= 8)
        <div wire:init="login"></div>
    @endif
</div>
