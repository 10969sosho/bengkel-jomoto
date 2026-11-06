{{--
    Tracker alur servis 12 tahap (booking hingga kwitansi).
    Pakai: @include('partials.service-flow', ['order' => $order])
--}}
@php
    $stages = $order->flowStages();
    $currentStage = $order->currentFlowStage();
@endphp

<div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5">
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Alur Servis</span>
        <span class="rounded-full bg-slate-900 px-2 py-0.5 text-[10px] font-bold text-lime-300">
            Tahap {{ $currentStage }} dari {{ count($stages) }}
        </span>
    </div>
    <ol class="flex flex-wrap gap-1.5">
        @foreach($stages as $stage)
            @php
                $isCurrent = $stage['no'] === $currentStage && ! $stage['done'];
                $classes = $stage['done']
                    ? 'bg-lime-100 text-lime-800 border-lime-200'
                    : ($isCurrent
                        ? 'bg-slate-900 text-lime-300 border-slate-900 ring-1 ring-lime-300/60'
                        : 'bg-white text-slate-400 border-slate-200');
            @endphp
            <li class="inline-flex items-center gap-1.5 rounded-lg border px-2 py-1 text-[11px] font-semibold {{ $classes }}">
                @if($stage['done'])
                    <i data-lucide="check" class="h-3 w-3"></i>
                @else
                    <span class="font-mono">{{ $stage['no'] }}</span>
                @endif
                {{ $stage['label'] }}
            </li>
        @endforeach
    </ol>
</div>
