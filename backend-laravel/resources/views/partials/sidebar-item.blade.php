<li>
    <a
        href="{{ route($route) }}"
        class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs($route) ? 'active' : '' }}"
    >
        <i class="bi {{ $icon }}" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
        <span>{{ $label }}</span>
    </a>
</li>