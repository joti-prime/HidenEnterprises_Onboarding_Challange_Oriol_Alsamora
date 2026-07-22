<button {{ $attributes->merge(['class' => 'btn ' . $class]) }}>
    {{ $slot }}
</button>
