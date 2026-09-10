<div class="kebab-container" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <button type="button" @click="open = !open" class="kebab-btn" title="Opsi Aksi">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
        </svg>
    </button>
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="kebab-dropdown" 
         style="display: none;">
        {{ $slot }}
    </div>
</div>
