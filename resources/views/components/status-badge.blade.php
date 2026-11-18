@props(['status'])

<span class="inline-flex items-center gap-1.5 rounded-sm px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-label ring-1 ring-inset {{ $status->badgeClasses() }}">
    <span class="inline-block h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
