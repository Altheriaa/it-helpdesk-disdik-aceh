<x-filament-panels::page>
    @php
        $metrics = $this->getFilteredMetrics();
    @endphp

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        .bay-app {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
        }

        .bay-app * {
            box-sizing: border-box;
        }

        /* ── Main Card ── */
        .bay-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }

        .dark .bay-card {
            background: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }

        .bay-card-header {
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            padding-bottom: 1rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .dark .bay-card-header {
            border-bottom-color: #334155;
        }

        .bay-icon-box {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            flex-shrink: 0;
        }

        .dark .bay-icon-box {
            background: #0f172a;
            border-color: #334155;
            color: #94a3b8;
        }

        .bay-header-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.3;
        }

        .dark .bay-header-title {
            color: #f8fafc;
        }

        .bay-header-subtitle {
            font-size: 0.8125rem;
            color: #64748b;
            margin-top: 0.15rem;
        }

        .dark .bay-header-subtitle {
            color: #94a3b8;
        }

        /* ── Metric Stat Cards Grid ── */
        .bay-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .bay-stat-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }

        .dark .bay-stat-card {
            background: #1e293b;
            border-color: #334155;
        }

        .bay-stat-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .dark .bay-stat-label {
            color: #94a3b8;
        }

        .bay-stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .dark .bay-stat-value {
            color: #f8fafc;
        }

        .bay-stat-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .bay-stat-icon.blue { background: #eff6ff; color: #2563eb; }
        .bay-stat-icon.amber { background: #fffbeb; color: #d97706; }
        .bay-stat-icon.sky { background: #f0f9ff; color: #0284c7; }
        .bay-stat-icon.emerald { background: #ecfdf5; color: #059669; }
        .bay-stat-icon.indigo { background: #eef2ff; color: #4f46e5; }
    </style>

    <div class="bay-app">
        <!-- ── Filter Card Container ── -->
        <div class="bay-card">
            <div class="bay-card-header">
                <div class="bay-icon-box">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="bay-header-title">Filter Periode & Data Laporan</h2>
                    <p class="bay-header-subtitle">Pilih rentang waktu dan kriteria untuk menampilkan data laporan rekapitulasi tiket</p>
                </div>
            </div>

            <!-- Form Livewire -->
            <div>
                {{ $this->form }}
            </div>
        </div>

        <!-- ── Ringkasan Metrik ── -->
        <div class="bay-stats-grid">
            <!-- Total Hasil -->
            <div class="bay-stat-card">
                <div>
                    <div class="bay-stat-label">Total Tiket</div>
                    <div class="bay-stat-value">{{ number_format($metrics['total']) }}</div>
                </div>
                <div class="bay-stat-icon blue">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>

            <!-- Open -->
            <div class="bay-stat-card">
                <div>
                    <div class="bay-stat-label">Baru / Antrian</div>
                    <div class="bay-stat-value" style="color: #d97706;">{{ number_format($metrics['open']) }}</div>
                </div>
                <div class="bay-stat-icon amber">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <!-- In Progress -->
            <div class="bay-stat-card">
                <div>
                    <div class="bay-stat-label">Sedang Diproses</div>
                    <div class="bay-stat-value" style="color: #0284c7;">{{ number_format($metrics['in_progress']) }}</div>
                </div>
                <div class="bay-stat-icon sky">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>

            <!-- Selesai -->
            <div class="bay-stat-card">
                <div>
                    <div class="bay-stat-label">Tiket Selesai</div>
                    <div class="bay-stat-value" style="color: #059669;">{{ number_format($metrics['resolved'] + $metrics['closed']) }}</div>
                </div>
                <div class="bay-stat-icon emerald">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Rasio Solusi -->
            <div class="bay-stat-card">
                <div>
                    <div class="bay-stat-label">Rasio Solusi</div>
                    <div class="bay-stat-value" style="color: #4f46e5;">{{ $metrics['rate'] }}%</div>
                </div>
                <div class="bay-stat-icon indigo">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- ── Table Container Card persis seperti Rincian Transaksi Pemasukan ── -->
        <div class="bay-card">
            <div class="bay-card-header">
                <div class="bay-icon-box">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="bay-header-title">Rincian Rekap Laporan Tiket</h2>
                    <p class="bay-header-subtitle">Daftar seluruh data tiket terfilter pada periode yang dipilih</p>
                </div>
            </div>

            <div>
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>