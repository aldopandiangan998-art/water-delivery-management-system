@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'description' => null,
    'disabled' => false,
])

@php
    $id = $id ?? $name ?? uniqid('checkbox-');
    $isChecked = old($name, $checked);
@endphp

<div class="flex items-start">
    <div class="flex h-6 items-center">
        <input 
            type="checkbox" 
            name="{{ $name }}" 
            id="{{ $id }}" 
            value="{{ $value }}"
            @if($isChecked) checked @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge([
                'class' => 'h-5 w-5 rounded-lg border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500/30 focus:ring-offset-0 transition cursor-pointer dark:bg-slate-900'
            ]) }}
        />
    </div>
    @if ($label)
        <div class="ml-3 text-xs sm:text-sm">
            <label for="{{ $id }}" class="font-semibold text-slate-800 dark:text-slate-200 cursor-pointer select-none">
                {{ $label }}
            </label>
            @if ($description)
                <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">
                    {{ $description }}
                </p>
            @endif
        </div>
    @endif
</div>
