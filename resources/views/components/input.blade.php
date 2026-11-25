@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'helper' => null,
    'icon' => null,
])

@php
    $id = $id ?? $name ?? uniqid('input-');
    $hasError = !empty($error) || ($name && $errors->has($name));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl shadow-2xs">
        @if ($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <i class="{{ $icon }} text-sm"></i>
            </div>
        @endif

        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $id }}" 
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-xl border text-xs sm:text-sm transition duration-150 min-h-[44px] ' .
                ($icon ? 'pl-10 ' : 'pl-3.5 ') . 'pr-3.5 py-2.5 ' .
                ($hasError 
                    ? 'border-rose-300 dark:border-rose-600 bg-rose-50/30 text-rose-900 dark:text-rose-200 focus:border-rose-500 focus:ring-rose-500/30 ' 
                    : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-cyan-500 focus:ring-cyan-500/30 ') .
                ($disabled ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 cursor-not-allowed ' : '') .
                'focus:outline-hidden focus:ring-3'
            ]) }}
        />
    </div>

    @if ($errorMessage)
        <p class="text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center mt-1">
            <i class="fa-solid fa-circle-exclamation mr-1.5 text-xs"></i>
            {{ $errorMessage }}
        </p>
    @elseif ($helper)
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            {{ $helper }}
        </p>
    @endif
</div>
