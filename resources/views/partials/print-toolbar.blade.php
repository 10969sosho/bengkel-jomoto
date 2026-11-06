{{--
    Toolbar cetak dengan pemilihan ukuran kertas (custom paper size).
    Dipakai pada halaman print mandiri (invoice/kwitansi & lembar estimasi):
        @include('partials.print-toolbar')
--}}
@php
    $paperPreset = $paperPreset ?? 'a4';
    $paperWidth = $paperWidth ?? 210;
    $paperHeight = $paperHeight ?? 297;
    $paperMargin = $paperMargin ?? 8;
@endphp

<style id="paper-rules">
    @page { size: {{ $paperWidth }}mm {{ $paperHeight }}mm; margin: {{ $paperMargin }}mm; }
</style>

<div class="no-print mx-auto mb-4 max-w-3xl rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label for="paper-preset" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Ukuran Kertas</label>
            <select id="paper-preset" class="mt-1 cursor-pointer rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-200">
                <option value="a4">A4 · 210 × 297 mm</option>
                <option value="a5">A5 · 148 × 210 mm</option>
                <option value="a6">A6 · 105 × 148 mm</option>
                <option value="thermal80">Struk / Thermal · 80 mm</option>
                <option value="thermal58">Struk / Thermal · 58 mm</option>
                <option value="custom">Custom (tentukan sendiri)</option>
            </select>
        </div>

        <div id="paper-custom" class="hidden items-end gap-2">
            <div>
                <label for="paper-width" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Lebar (mm)</label>
                <input id="paper-width" type="number" min="20" max="1000" value="{{ $paperWidth }}"
                       class="mt-1 w-24 rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label for="paper-height" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Tinggi (mm)</label>
                <input id="paper-height" type="number" min="20" max="1000" value="{{ $paperHeight }}"
                       class="mt-1 w-24 rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
            </div>
        </div>

        <div>
            <label for="paper-margin" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Margin (mm)</label>
            <input id="paper-margin" type="number" min="0" max="50" value="{{ $paperMargin }}"
                   class="mt-1 w-24 rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
        </div>

        <div class="ml-auto flex flex-wrap gap-2">
            <a href="{{ url()->previous() }}" class="rounded-xl bg-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">
                ← Kembali
            </a>
            <button type="button" id="paper-print"
                    class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                🖨 Cetak / Simpan PDF
            </button>
        </div>
    </div>
    <p class="mt-2.5 text-[11px] leading-relaxed text-slate-400">
        Ukuran kertas custom diterapkan saat mencetak — printer harus mendukung pengaturan ukuran kertas.
        Pengaturan terakhir tersimpan di browser ini.
    </p>
</div>

<script>
    (function () {
        const KEY = 'jomoto.paper';
        const presets = {
            a4: { w: 210, h: 297 },
            a5: { w: 148, h: 210 },
            a6: { w: 105, h: 148 },
            thermal80: { w: 80, h: null },
            thermal58: { w: 58, h: null },
        };

        const presetSelect = document.getElementById('paper-preset');
        const customBox = document.getElementById('paper-custom');
        const widthInput = document.getElementById('paper-width');
        const heightInput = document.getElementById('paper-height');
        const marginInput = document.getElementById('paper-margin');
        const rules = document.getElementById('paper-rules');

        let saved = null;
        try { saved = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) { saved = null; }
        if (saved && saved.preset) {
            presetSelect.value = saved.preset;
            if (saved.preset === 'custom') {
                widthInput.value = saved.w || 210;
                heightInput.value = saved.h || 297;
            }
            marginInput.value = saved.margin ?? 8;
        }

        function apply() {
            const preset = presetSelect.value;
            const useCustom = preset === 'custom';
            customBox.classList.toggle('hidden', !useCustom);

            const size = useCustom
                ? { w: Number(widthInput.value) || 210, h: Number(heightInput.value) || 297 }
                : presets[preset];
            const margin = Math.min(50, Math.max(0, Number(marginInput.value) || 0));
            const page = size.h ? size.w + 'mm ' + size.h + 'mm' : size.w + 'mm';

            rules.textContent = '@page { size: ' + page + '; margin: ' + margin + 'mm; }';
            document.body.classList.toggle('paper-narrow', size.w < 130);
            localStorage.setItem(KEY, JSON.stringify({ preset: preset, w: size.w, h: size.h, margin: margin }));
        }

        [presetSelect, widthInput, heightInput, marginInput].forEach(el => el.addEventListener('change', apply));
        [widthInput, heightInput, marginInput].forEach(el => el.addEventListener('input', apply));
        document.getElementById('paper-print').addEventListener('click', () => window.print());

        apply();
    })();
</script>
