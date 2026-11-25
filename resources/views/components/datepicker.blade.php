@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => 'Pilih tanggal...',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'helper' => null,
    'enableTime' => false,
    'dateFormat' => 'Y-m-d',
])

@php
    $id = $id ?? $name ?? uniqid('datepicker-');
    $hasError = !empty($error) || ($name && $errors->has($name));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full space-y-1.5"
     x-data="{
         initPicker() {
             if (typeof flatpickr !== 'undefined') {
                 flatpickr(this.$refs.pickerInput, {
                     dateFormat: '{{ $dateFormat }}',
                     enableTime: {{ $enableTime ? 'true' : 'false' }},
                     altInput: true,
                     altFormat: '{{ $enableTime ? 'd M Y, H:i' : 'd M Y' }}',
                     locale: 'id',
                     theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                 });
             }
         }
     }"
     x-init="initPicker()">
    
    @if ($label)
        <label for="{{ $id }}" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl shadow-2xs">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
            <i class="fa-regular fa-calendar text-sm"></i>
        </div>

        <input 
            type="text" 
            name="{{ $name }}" 
            id="{{ $id }}" 
            x-ref="pickerInput"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-xl border text-xs sm:text-sm transition duration-150 min-h-[44px] pl-10 pr-3.5 py-2.5 ' .
                ($hasError 
                    ? 'border-rose-300 dark:border-rose-600 bg-rose-50/30 text-rose-900 dark:text-rose-200 focus:border-rose-500 focus:ring-rose-500/30 ' 
                    : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 ') .
                'focus:outline-hidden disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed'
            ]) }}
        />
    </div>

    @if ($errorMessage)
        <p class="text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center mt-1">
            <i class="fa-solid fa-circle-exclamation mr-1.5 text-xs"></i>
            {{ $errorMessage }}
        </p>
    @elseif ($helper)
        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $helper }}</p>
    @endif
</div>
