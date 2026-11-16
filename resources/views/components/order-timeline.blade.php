@props(['order'])

@php
    $events = $order->histories;
@endphp

@if ($events->isEmpty())
    <p class="text-sm text-slate-500">No status updates recorded yet.</p>
@else
    <ol class="relative space-y-6 border-l border-slate-200 pl-6">
        @foreach ($events as $event)
            <li class="relative">
                <span class="absolute -left-[1.85rem] top-1.5 h-3 w-3 rounded-full ring-4 {{ $event->status->dotClasses() }}"
                      aria-hidden="true"></span>

                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                    <p class="text-sm font-semibold text-slate-900">{{ $event->status->timelineLabel() }}</p>
                    <time class="text-xs text-slate-400" datetime="{{ $event->created_at->toIso8601String() }}">
                        {{ $event->created_at->format('d M Y, h:i A') }}
                    </time>
                </div>

                <p class="mt-0.5 text-xs text-slate-500">
                    {{ $event->actorLabel($order) }} &middot; {{ $event->created_at->diffForHumans() }}
                </p>

                @if ($event->note)
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $event->note }}</p>
                @endif
            </li>
        @endforeach
    </ol>
@endif
