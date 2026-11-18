@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border-line bg-white text-ink shadow-inner focus:border-forest-600 focus:ring-forest-600']) }}>
