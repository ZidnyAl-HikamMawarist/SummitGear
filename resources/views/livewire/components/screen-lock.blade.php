<div>
    @if(auth()->check() && auth()->user()->role === 'kasir')
    <flux:modal wire:model="isLocked" :dismissible="false" :closable="false" class="max-w-sm text-center p-8 rounded-3xl bg-white shadow-2xl border border-slate-100">
        <div class="mb-4 flex justify-center">
            <div class="w-16 h-16 rounded-2xl bg-orange-50 border border-orange-200/80 text-orange-600 flex items-center justify-center shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
        </div>

        <flux:heading size="xl" class="mb-1 font-black text-slate-900">Layar Terkunci</flux:heading>
        <flux:subheading class="mb-5 text-xs text-slate-500">Masukkan PIN Anda untuk membuka kembali layar ini.</flux:subheading>

        <form wire:submit.prevent="unlock" class="space-y-4">
            <div>
                <flux:input 
                    type="password" 
                    wire:model="pin"
                    class:input="text-center text-2xl tracking-[0.5em] font-mono font-bold"
                    placeholder="••••••"
                    autofocus
                />
                @if($errorMessage)
                    <p class="mt-2 text-xs font-bold text-red-500">{{ $errorMessage }}</p>
                @endif
            </div>

            <flux:button type="submit" variant="primary" class="w-full font-bold">
                Buka Kunci
            </flux:button>
        </form>
    </flux:modal>
    @endif
</div>
