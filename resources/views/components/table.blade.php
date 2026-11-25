@props([
    'headers' => [],
])

<div class="overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left text-xs">
            @if (!empty($headers))
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-400 font-bold uppercase tracking-wider">
                    <tr>
                        @foreach ($headers as $header)
                            <th class="px-5 py-3.5">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
