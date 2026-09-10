<div>
    <flux:modal wire:model="isOpen" class="max-w-sm">
        <div class="mb-3 flex justify-center">
            <div class="w-14 h-14 rounded-2xl bg-orange-50 dark:bg-orange-950/40 text-coral flex items-center justify-center shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>
        
        <flux:heading size="lg" class="text-center font-bold mb-1">Otorisasi Admin</flux:heading>
        <flux:subheading class="text-center text-xs mb-5">Tindakan ini memerlukan verifikasi PIN Super Admin.</flux:subheading>

        <form wire:submit.prevent="verify" class="space-y-4">
            <div>
                <flux:input 
                    type="password" 
                    wire:model="pin"
                    class:input="text-center text-xl tracking-[0.5em] font-mono font-bold"
                    placeholder="••••••"
                    autofocus
                />
                @if($errorMessage)
                    <p class="mt-2 text-xs font-bold text-red-500 text-center">{{ $errorMessage }}</p>
                @endif
            </div>

            <div class="flex gap-2.5 pt-2">
                <flux:button type="button" variant="ghost" wire:click="closeModal" class="flex-1">
                    Batal
                </flux:button>
                <flux:button type="submit" variant="primary" class="flex-1 font-bold">
                    Verifikasi
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
