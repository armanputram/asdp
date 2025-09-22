{{-- resources/views/components/layanan-selector.blade.php --}}

<div class="layanan-selector-container">
    <style>
        .layanan-selector-container {
            padding: 20px;
        }

        .layanan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .layanan-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 25px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border: 2px solid transparent;
        }

        .layanan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
        }

        .layanan-card.active {
            border-color: #3b82f6;
            transform: translateY(-3px) scale(1.02);
        }

        .layanan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #00f5ff, #ffd700, #ff6b6b);
        }

        .loket-pejalan { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .tolgate-r4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .tolgate-r2 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .loket-bulusan { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
        .rak-server { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

        .service-icon {
            font-size: 40px;
            margin-bottom: 12px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .service-title {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .service-count {
            font-size: 0.9em;
            opacity: 0.8;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 12px;
        }
    </style>

    <div class="layanan-grid">
        @php
            $layananList = [
                [
                    'name' => 'LOKET PEJALAN KAKI',
                    'icon' => '🚶',
                    'class' => 'loket-pejalan',
                    'desc' => 'Layanan pejalan kaki'
                ],
                [
                    'name' => 'TOLGATE (R4, TRUCK, LCM)',
                    'icon' => '🚛',
                    'class' => 'tolgate-r4',
                    'desc' => 'Kendaraan berat'
                ],
                [
                    'name' => 'TOLGATE (R2)',
                    'icon' => '🚗',
                    'class' => 'tolgate-r2',
                    'desc' => 'Kendaraan roda 2'
                ],
                [
                    'name' => 'LOKET BULUSAN',
                    'icon' => '🎫',
                    'class' => 'loket-bulusan',
                    'desc' => 'Tiket khusus'
                ],
                [
                    'name' => 'RAK SERVER E-Ticketing',
                    'icon' => '🖥️',
                    'class' => 'rak-server',
                    'desc' => 'Sistem elektronik'
                ]
            ];
        @endphp

        @foreach($layananList as $layanan)
            @php
                $count = \App\Models\Operasional::whereHas('layanan', function($query) use ($layanan) {
                    $query->where('nama', $layanan['name']);
                })->count();
            @endphp

            <div class="layanan-card {{ $layanan['class'] }}"
                 data-layanan="{{ $layanan['name'] }}"
                 onclick="filterByLayanan('{{ $layanan['name'] }}', this)">
                <div class="service-icon">{{ $layanan['icon'] }}</div>
                <div class="service-title">{{ $layanan['name'] }}</div>
                <div class="service-count">{{ $count }} Record</div>
            </div>
        @endforeach
    </div>

    <script>
        function filterByLayanan(layananName, cardElement) {
            // Remove active class from all cards
            document.querySelectorAll('.layanan-card').forEach(card => {
                card.classList.remove('active');
            });

            // Add active class to clicked card
            cardElement.classList.add('active');

            // Redirect ke halaman dengan filter layanan
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('tableFilters[layanan][value]', layananName);
            window.location.href = currentUrl.toString();
        }

        // Check if there's an active filter and highlight the corresponding card
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const activeLayanan = urlParams.get('tableFilters[layanan][value]');

            if (activeLayanan) {
                const activeCard = document.querySelector(`[data-layanan="${activeLayanan}"]`);
                if (activeCard) {
                    activeCard.classList.add('active');
                }
            }
        });
    </script>
</div>
