@props(['order'])

@php
    $events = $order->histories;
@endphp

@if ($events->isEmpty())
    <p class="font-mono text-xs uppercase tracking-label text-ink-faint">No status updates recorded yet.</p>
@else
    <ol class="relative space-y-5 border-l border-dashed border-line-dark pl-6">
        @foreach ($events as $event)
            <li class="relative">
                <span class="absolute -left-[1.85rem] top-1.5 h-3 w-3 rounded-full ring-4 {{ $event->status->dotClasses() }}"
                      aria-hidden="true"></span>

                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                    <p class="font-display text-[15px] font-semibold text-ink">{{ $event->status->timelineLabel() }}</p>
                    <time class="font-mono text-[9px] uppercase tracking-label text-ink-faint" datetime="{{ $event->created_at->toIso8601String() }}">
                        {{ $event->created_at->format('d M Y, h:i A') }}
                    </time>
                </div>

                <p class="mt-0.5 text-xs text-ink-soft">
                    {{ $event->actorLabel($order) }} &middot; {{ $event->created_at->diffForHumans() }}
                </p>

                @if ($event->note)
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-soft">{{ $event->note }}</p>
                @endif
            </li>
        @endforeach
    </ol>
@endif
