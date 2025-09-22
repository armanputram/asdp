<x-filament-widgets::widget>
    <!-- Header dengan tombol reset jika ada layanan terpilih -->
    @if($selectedLayanan)
        <div class="flex justify-between items-center mb-4 p-4 bg-blue-50 rounded-lg">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Layanan Terpilih: <span class="text-blue-600">{{ $selectedLayanan }}</span>
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    Klik "Reset" untuk memilih layanan lain
                </p>
            </div>
            <button
                wire:click="resetFilter"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                style="background-color: #ef4444; border: 2px solid #dc2626; color: white;"
                onmouseover="this.style.backgroundColor='#dc2626'"
                onmouseout="this.style.backgroundColor='#ef4444'"
            >
                ✕ Reset Filter
            </button>
        </div>
    @else
        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                Pilih Layanan Operasional
            </h3>
            <p class="text-sm text-gray-600">
                Klik pada kartu layanan di bawah untuk menampilkan data operasional
            </p>
        </div>
    @endif  

    <div class="layanan-selector-grid p-6 bg-white rounded-xl">
        <style>
            .layanan-selector-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 20px;
                margin-bottom: 2rem;
            }

            .layanan-card {
                cursor: pointer;
                transition: all 0.3s ease;
                border-radius: 16px;
                padding: 24px;
                text-align: center;
                color: rgb(0, 0, 0);
                position: relative;
                overflow: hidden;
                min-height: 140px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                border: 3px solid transparent;
            }

            .layanan-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            }

            .layanan-card.selected {
                border-color: #3b82f6;
                transform: translateY(-8px);
                box-shadow: 0 20px 40px rgba(59, 130, 246, 0.3);
            }

            .layanan-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #fbbf24, #f59e0b, #d97706);
            }

            .layanan-card.selected::before {
                background: linear-gradient(90deg, #3b82f6, #1d4ed8, #1e40af);
                height: 6px;
            }

            .card-icon {
                font-size: 48px;
                margin-bottom: 12px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                width: 80px;
                height: 80px;
                display: flex;
                align-items: center;
                justify-content: center;
                backdrop-filter: blur(10px);
                position: relative;
            }

            .card-title {
                font-size: 16px;
                font-weight: 600;
                margin-bottom: 8px;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
                line-height: 1.2;
            }

            .card-count {
                font-size: 14px;
                opacity: 0.9;
                background: rgba(255, 255, 255, 0.25);
                padding: 4px 12px;
                border-radius: 20px;
                backdrop-filter: blur(5px);
            }

            .selected-indicator {
                position: absolute;
                top: -2px;
                right: -2px;
                background: #3b82f6;
                color: white;
                border-radius: 50%;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                font-weight: bold;
                z-index: 10;
            }

            @media (max-width: 768px) {
                .layanan-selector-grid {
                    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                    gap: 16px;
                }

                .layanan-card {
                    padding: 20px;
                    min-height: 120px;
                }

                .card-icon {
                    width: 64px;
                    height: 64px;
                    font-size: 36px;
                }
            }
        </style>

        @foreach($layananOptions as $layanan)
            <div class="layanan-card {{ $layanan['color'] }} {{ $selectedLayanan === $layanan['name'] ? 'selected' : '' }}"
                 wire:click="selectLayanan('{{ $layanan['name'] }}')">
                <div class="card-icon">
                    {{ $layanan['icon'] }}
                    @if($selectedLayanan === $layanan['name'])
                        <div class="selected-indicator">
                            ✓
                        </div>
                    @endif
                </div>
                <div class="card-title">
                    {{ $layanan['name'] }}
                </div>
                <div class="card-count">
                    {{ $layanan['count'] }} Data
                </div>
            </div>
        @endforeach
    </div>

    @if(!$selectedLayanan)
        <div class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
            <div class="text-center">
                <div class="text-4xl mb-2">📊</div>
                <h4 class="font-semibold text-gray-900 mb-2">Belum Ada Layanan Dipilih</h4>
                <p class="text-gray-600 text-sm">
                    Klik salah satu kartu layanan di atas untuk menampilkan tabel data operasional yang sesuai.
                </p>
            </div>
        </div>
    @endif
</x-filament-widgets::widget>
