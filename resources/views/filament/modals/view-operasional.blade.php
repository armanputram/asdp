<div class="space-y-6 p-1">

    {{-- Header Info --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pelabuhan</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ optional($record->pelabuhan)->nama ?? '-' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Cabang</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ optional($record->cabang)->nama ?? '-' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ \Carbon\Carbon::parse($record->created_at)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Lokasi</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ count($loketData) }} lokasi dikerjakan
                </p>
            </div>
        </div>

        {{-- ══ STATUS VALIDASI + TOMBOL ══ --}}
        <div class="mt-4 flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-600 dark:bg-gray-700/50">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Status Validasi</p>
                @if ($record->is_validated)
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">
                        ✅ Tervalidasi
                    </span>
                    <p class="mt-0.5 text-xs text-gray-400">
                        oleh {{ optional($record->validatedBy)->name ?? '-' }}
                        @if ($record->validated_at)
                            · {{ \Carbon\Carbon::parse($record->validated_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}
                        @endif
                    </p>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-600 dark:bg-orange-900/40 dark:text-orange-300">
                        ⏳ Menunggu Validasi
                    </span>
                    <p class="mt-0.5 text-xs text-gray-400">Belum divalidasi — Export PDF tidak tersedia</p>
                @endif
            </div>

            @if ($canValidate)
                @if (! $record->is_validated)
                    <button
                        id="btn-validasi"
                        onclick="handleValidasi({{ $record->id }}, 'validasi')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-yellow-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-yellow-600 focus:outline-none active:scale-95 transition-all"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.955 11.955 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        Validasi Sekarang
                    </button>
                @else
                    <button
                        id="btn-validasi"
                        onclick="handleValidasi({{ $record->id }}, 'batalkan')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-600 focus:outline-none active:scale-95 transition-all"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Batalkan Validasi
                    </button>
                @endif
            @endif
        </div>
        {{-- ══ END STATUS VALIDASI ══ --}}
    </div>

    @if (empty($loketData))
        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-400 dark:border-gray-600">
            <x-heroicon-o-inbox class="mx-auto mb-2 h-8 w-8" />
            <p class="text-sm">Belum ada data operasional yang diisi.</p>
        </div>
    @endif

    {{-- ══ LOKET CARDS ══════════════════════════════════════════════ --}}
    @foreach ($loketData as $key => $entry)
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">

            {{-- Header: Loket + Layanan --}}
            <div class="flex items-center gap-3 bg-primary-600 px-5 py-3 dark:bg-primary-800">
                <x-heroicon-o-map-pin class="h-5 w-5 text-white" />
                <div class="flex flex-col">
                    <span class="text-base font-bold text-white">Loket {{ $entry['loket'] }}</span>
                    <span class="text-xs font-medium text-primary-200">{{ $entry['layanan'] }}</span>
                </div>
                <span class="ml-auto rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium text-white">
                    {{ collect($entry['petugas'])->sum(fn($p) => count($p['items'])) }} item
                </span>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($entry['petugas'] as $petugas)
                    <div class="bg-white dark:bg-gray-800">

                        {{-- Petugas Sub-Header --}}
                        <div class="flex items-center gap-2 bg-gray-50 px-5 py-2.5 dark:bg-gray-700/50">
                            <x-heroicon-o-user-circle class="h-4 w-4 text-gray-400" />
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                {{ $petugas['nama_petugas'] }}
                            </span>
                            <span class="ml-auto text-xs text-gray-400">
                                {{ count($petugas['items']) }} perangkat
                            </span>
                        </div>

                        {{-- Tabel Item --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-100 text-xs uppercase text-gray-500 dark:bg-gray-700/60 dark:text-gray-400">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Perangkat</th>
                                        <th class="px-4 py-2 text-center">Status</th>
                                        <th class="px-4 py-2 text-left">Catatan</th>
                                        <th class="px-4 py-2 text-center">Foto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    @foreach ($petugas['items'] as $item)
                                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                                {{ $item['perangkat'] }}
                                            </td>
                                            <td class="px-4 py-2.5 text-center">
                                                @if (! $item['foto'])
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-600 dark:bg-orange-900/40 dark:text-orange-300">
                                                        ⏳ Belum Dikerjakan
                                                    </span>
                                                @elseif ($item['status'] === 'rusak')
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-600 dark:bg-red-900/40 dark:text-red-300">
                                                        ❌ Rusak
                                                    </span>
                                                @elseif ($item['status'] === 'baik')
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-600 dark:bg-green-900/40 dark:text-green-300">
                                                        ✅ Baik
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                {{ $item['catatan'] ?: '—' }}
                                            </td>
                                            <td class="px-2 py-2 text-center" style="width:90px; min-width:90px;">
                                                @if ($item['foto'])
                                                    <img
                                                        src="{{ $item['foto'] }}"
                                                        alt="{{ $item['perangkat'] }}"
                                                        data-foto="{{ $item['foto'] }}"
                                                        data-nama="{{ $item['perangkat'] }}"
                                                        class="foto-thumb"
                                                        style="width:64px;height:64px;object-fit:cover;border-radius:8px;cursor:zoom-in;display:block;margin:0 auto;"
                                                    />
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
    {{-- ══ END LOKET CARDS ══════════════════════════════════════════ --}}


    {{-- ══ PERANGKAT BELUM DIKERJAKAN ════════ --}}
    @if (! empty($belumDikerjakan))
        <div class="overflow-hidden rounded-xl border border-orange-300 dark:border-orange-700">

            <div class="flex items-center gap-2 bg-orange-500 px-5 py-3">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-white" />
                <span class="text-base font-bold text-white">Perangkat Belum Dikerjakan</span>
                <span class="ml-auto rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium text-white">
                    {{ collect($belumDikerjakan)->sum(fn ($p) => count($p)) }} perangkat
                </span>
            </div>

            <div class="divide-y divide-orange-100 bg-orange-50 dark:divide-orange-800 dark:bg-orange-900/20">
                @foreach ($belumDikerjakan as $layananNama => $perangkatList)
                    <div class="px-5 py-4">
                        <p class="mb-3 flex items-center gap-2 text-sm font-semibold text-orange-700 dark:text-orange-300">
                            <x-heroicon-o-tag class="h-4 w-4 shrink-0" />
                            {{ $layananNama }}
                            <span class="ml-1 rounded-full bg-orange-200 px-2 py-0.5 text-xs font-medium text-orange-700 dark:bg-orange-800 dark:text-orange-200">
                                {{ count($perangkatList) }} belum
                            </span>
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($perangkatList as $nama)
                                <span class="inline-flex items-center gap-1 rounded-full border border-orange-300 bg-white px-3 py-1 text-xs font-medium text-orange-700 dark:border-orange-600 dark:bg-orange-900/40 dark:text-orange-200">
                                    <x-heroicon-o-clock class="h-3 w-3 shrink-0" />
                                    {{ $nama }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    {{-- ══ END BELUM DIKERJAKAN ════════════════════════════════════ --}}

</div>

<script>
(function () {
    // ── Foto lightbox ────────────────────────────────────────────────
    if (window._fotoLbReady) return;
    window._fotoLbReady = true;

    window.addEventListener('click', function (e) {
        var img = e.target.closest ? e.target.closest('.foto-thumb') : null;
        if (!img) return;

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        var src  = img.getAttribute('data-foto');
        var nama = img.getAttribute('data-nama');

        var old = document.getElementById('_foto_lb');
        if (old) old.remove();

        var overlay = document.createElement('div');
        overlay.id = '_foto_lb';
        overlay.style.cssText = 'position:fixed;inset:0;z-index:2147483647;background:rgba(0,0,0,.92);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;cursor:zoom-out';

        var closeBtn = document.createElement('button');
        closeBtn.textContent = '✕';
        closeBtn.style.cssText = 'position:fixed;top:16px;right:20px;background:rgba(255,255,255,.2);border:none;border-radius:999px;width:36px;height:36px;color:white;font-size:18px;cursor:pointer;z-index:2147483647';

        var bigImg = document.createElement('img');
        bigImg.src = src;
        bigImg.style.cssText = 'max-width:90vw;max-height:85vh;object-fit:contain;border-radius:10px;box-shadow:0 20px 60px rgba(0,0,0,.7);cursor:default';
        bigImg.addEventListener('click', function(ev){ ev.stopPropagation(); }, true);

        var cap = document.createElement('p');
        cap.textContent = nama;
        cap.style.cssText = 'margin-top:12px;color:rgba(255,255,255,.7);font-size:13px;text-align:center';
        cap.addEventListener('click', function(ev){ ev.stopPropagation(); }, true);

        function tutup(ev) {
            if (ev) { ev.stopPropagation(); ev.preventDefault(); }
            overlay.remove();
            window.removeEventListener('keydown', escFn, true);
        }

        var escFn = function(ev) { if (ev.key === 'Escape') tutup(); };

        closeBtn.addEventListener('click', tutup, true);
        overlay.addEventListener('click', tutup, true);

        overlay.appendChild(closeBtn);
        overlay.appendChild(bigImg);
        overlay.appendChild(cap);
        document.body.appendChild(overlay);
        window.addEventListener('keydown', escFn, true);

    }, true);
})();

// ── Validasi handler ─────────────────────────────────────────────────
function handleValidasi(recordId, aksi) {
    var pesan = aksi === 'validasi'
        ? 'Validasi laporan ini? Tanda tangan Anda akan tercatat pada PDF.'
        : 'Batalkan validasi? Laporan tidak bisa diekspor sebelum divalidasi ulang.';

    if (!confirm(pesan)) return;

    var btn = document.getElementById('btn-validasi');
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.style.cursor  = 'not-allowed';
    }

    // Ambil CSRF token dari meta tag (standar Laravel)
    var csrf = document.querySelector('meta[name="csrf-token"]');

    fetch('/operasional/' + recordId + '/validasi', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ aksi: aksi }),
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            // Refresh Livewire component (Filament pakai Livewire)
            if (window.Livewire) {
                window.Livewire.dispatch('$refresh');
            }
            // Tutup modal setelah sebentar biar refresh sempat jalan
            setTimeout(function() {
                var closeEls = document.querySelectorAll('[data-modal-close], button[x-on\\:click*="close"]');
                if (closeEls.length) closeEls[closeEls.length - 1].click();
                else location.reload();
            }, 400);
        } else {
            alert(data.message ?? 'Terjadi kesalahan.');
            if (btn) { btn.disabled = false; btn.style.opacity = '1'; btn.style.cursor = ''; }
        }
    })
    .catch(function() {
        alert('Gagal menghubungi server. Coba lagi.');
        if (btn) { btn.disabled = false; btn.style.opacity = '1'; btn.style.cursor = ''; }
    });
}
</script>
