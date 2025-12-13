@props(['name', 'size' => 20])

@php
$icons = [
    'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke-width="2"/>',
    'mail' => '<rect x="2" y="4" width="20" height="16" rx="2" stroke-width="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" stroke-width="2"/>',
    'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke-width="2"/>',
    'briefcase' => '<path d="M12 2v20M2 12h20M17 12a5 5 0 0 1-5 5V7a5 5 0 0 1 5 5z" stroke-width="2"/>',
    'map-pin' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-width="2"/><circle cx="12" cy="10" r="3" stroke-width="2"/>',
    'hash' => '<path d="M16 22H6c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h12a2 2 0 0 1 2 2v8" stroke-width="2"/><path d="M14 2v6h6" stroke-width="2"/><path d="M15 17h6" stroke-width="2"/><path d="M18 14v6" stroke-width="2"/>',
    'globe' => '<circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" stroke-width="2"/><path d="M2 12h20" stroke-width="2"/>',
    'file-text' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-width="2"/><polyline points="14 2 14 8 20 8" stroke-width="2"/>',
    'clock' => '<circle cx="12" cy="12" r="10" stroke-width="2"/><polyline points="12 6 12 12 16 14" stroke-width="2"/>',
];
@endphp

<svg width="{{ $size }}" height="{{ $size }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    {!! $icons[$name] ?? '' !!}
</svg>