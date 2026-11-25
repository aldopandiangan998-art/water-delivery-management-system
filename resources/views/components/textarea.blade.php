@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 3,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'helper' => null,
])

@php
    $id = $id ?? $name ?? uniqid('textarea-');
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

    <textarea 
        name="{{ $name }}" 
        id="{{ $id }}" 
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-xl border text-xs sm:text-sm p-3.5 transition duration-150 ' .
            ($hasError 
                ? 'border-rose-300 dark:border-rose-600 bg-rose-50/30 text-rose-900 dark:text-rose-200 focus:border-rose-500 focus:ring-rose-500/30 ' 
                : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-cyan-500 focus:ring-cyan-500/30 ') .
            ($disabled ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 cursor-not-allowed ' : '') .
            'focus:outline-hidden focus:ring-3'
        ]) }}>{{ old($name, $value) }}</textarea>

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
