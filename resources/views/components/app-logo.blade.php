@props([
    'size' => 'md',
    'variant' => 'icon',
    'textClass' => 'text-navy',
    'rounded' => 'rounded-xl',
])

@php
    $sizeClasses = match($size) {
        'xs' => 'w-5 h-5',
        'sm' => 'w-7 h-7',
        'md' => 'w-9 h-9',
        'lg' => 'w-12 h-12',
        'xl' => 'w-16 h-16',
        default => $size,
    };

    $textSize = match($size) {
        'xs' => 'text-xs',
        'sm' => 'text-sm',
        'md' => 'text-base',
        'lg' => 'text-xl',
        'xl' => 'text-2xl',
        default => 'text-base',
    };

    $subtextSize = match($size) {
        'xs', 'sm' => 'text-[9px]',
        'md' => 'text-[11px]',
        'lg' => 'text-xs',
        'xl' => 'text-sm',
        default => 'text-[11px]',
    };
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 select-none']) }}>
    <!-- Official SummitGear Mark (Pixel-Perfect SVG) -->
    <div class="{{ $sizeClasses }} shrink-0 flex items-center justify-center shadow-xs overflow-hidden transition-transform duration-200 hover:scale-105"
         style="background: #0B1528; border-radius: 28%;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" class="w-full h-full p-[14%]" fill="none">
            <!-- Mountain Peak Base Triangle -->
            <path d="M 50 14
                     L 85 78
                     A 4 4 0 0 1 81.5 83
                     L 18.5 83
                     A 4 4 0 0 1 15 78
                     Z" 
                  stroke="#FF4D00" 
                  stroke-width="12.5" 
                  stroke-linejoin="round" 
                  stroke-linecap="round" />
            <!-- Dynamic Mountain Trail / Ascending Ridge Slash -->
            <path d="M 36 60 L 65 48" 
                  stroke="#FF4D00" 
                  stroke-width="12.5" 
                  stroke-linecap="round" />
        </svg>
    </div>

    @if($variant === 'full')
        <div class="flex flex-col text-left leading-none">
            <span class="font-extrabold tracking-tight {{ $textSize }} {{ $textClass }}">
                Summit<span style="color: #FF4D00;">Gear</span>
            </span>
            <span class="{{ $subtextSize }} font-semibold text-slate-400 mt-0.5 tracking-normal">Outdoor POS & Rental</span>
        </div>
    @endif
</div>
