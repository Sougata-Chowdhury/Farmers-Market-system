@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-mono text-[11px] font-bold uppercase tracking-label text-ink-soft']) }}>
    {{ $value ?? $slot }}
</label>
