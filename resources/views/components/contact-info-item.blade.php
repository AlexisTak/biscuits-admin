@props(['icon', 'label', 'value' => null, 'href' => null])

<div class="info-item">
    <span class="info-label">
        <x-icon :name="$icon" />
        {{ $label }}
    </span>
    
    @if($slot->isEmpty())
        <span class="info-value">
            @if($href)
                <a href="{{ $href }}" class="email-link">{{ $value }}</a>
            @else
                {{ $value }}
            @endif
        </span>
    @else
        {{ $slot }}
    @endif
</div>