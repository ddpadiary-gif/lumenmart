@props(['product', 'size' => 'md'])

@php
    $hasFile = filled($product->image_path) && file_exists(public_path($product->image_path));
    $catSlug = $product->category->slug ?? 'sembako';
    $catName = $product->category->name ?? 'Lumen Mart';
    $initials = $product->initials();

    $palette = match ($catSlug) {
        'sembako' => ['bg' => '#f5f5f7', 'accent' => '#1d1d1f', 'chip' => '#e8e8ed'],
        'minuman-kopi' => ['bg' => '#f4f1ee', 'accent' => '#3a2e28', 'chip' => '#e6dfd9'],
        'makanan-instan' => ['bg' => '#f7f4ef', 'accent' => '#2c2520', 'chip' => '#ebe4d8'],
        'bumbu-dapur' => ['bg' => '#f3f5f2', 'accent' => '#253028', 'chip' => '#dfe6dc'],
        'kebersihan-rumah' => ['bg' => '#f0f5fa', 'accent' => '#0071e3', 'chip' => '#dce9f7'],
        default => ['bg' => '#f5f5f7', 'accent' => '#1d1d1f', 'chip' => '#e8e8ed'],
    };
@endphp

@if ($hasFile)
    <img
        src="{{ asset($product->image_path) }}"
        alt="{{ $product->name }}"
        loading="lazy"
        {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}
    />
@else
    <div
        {{ $attributes->merge(['class' => 'relative flex h-full w-full flex-col items-center justify-between overflow-hidden p-4 select-none']) }}
        style="background-color: {{ $palette['bg'] }};"
    >
        <div class="flex w-full items-center justify-between text-[10px] font-semibold uppercase tracking-wider text-[#86868b]">
            <span class="truncate">{{ $catName }}</span>
            <span class="font-mono text-[9px] opacity-75">{{ $product->unit }}</span>
        </div>

        <svg class="my-auto h-16 w-16 shrink-0" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="6" y="6" width="68" height="68" rx="22" fill="{{ $palette['chip'] }}" />
            <circle cx="40" cy="34" r="14" stroke="{{ $palette['accent'] }}" stroke-width="1.6" stroke-opacity="0.22" fill="white" fill-opacity="0.7" />
            <text
                x="40"
                y="39"
                text-anchor="middle"
                fill="{{ $palette['accent'] }}"
                font-family="Inter, -apple-system, BlinkMacSystemFont, sans-serif"
                font-size="13"
                font-weight="700"
                letter-spacing="-0.03em"
            >{{ $initials }}</text>
            <rect x="22" y="55" width="36" height="4" rx="2" fill="{{ $palette['accent'] }}" fill-opacity="0.16" />
        </svg>

        <p class="w-full truncate text-center text-[11px] font-medium tracking-tight text-[#1d1d1f]/80">
            {{ $product->name }}
        </p>
    </div>
@endif
