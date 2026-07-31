@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-neutral-50 border-neutral-300 focus:border-accent focus:ring-accent rounded-md shadow-sm']) }}>
