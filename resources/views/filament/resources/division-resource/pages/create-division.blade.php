<x-filament-panels::page>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        .bay-app {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
        }

        .bay-app * {
            box-sizing: border-box;
        }

        .bay-app a {
            text-decoration: none;
            color: inherit;
        }

        /* ── Card Styling ── */
        .bay-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            margin-bottom: 1.5rem;
        }

        .dark .bay-card {
            background: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }

        .bay-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .dark .bay-card-header {
            border-bottom-color: #334155;
        }

        .bay-icon-title-group {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .bay-card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.3;
        }

        .dark .bay-card-title {
            color: #f8fafc;
        }

        .bay-card-subtitle {
            font-size: 0.8125rem;
            color: #64748b;
            margin-top: 0.2rem;
        }

        .dark .bay-card-subtitle {
            color: #94a3b8;
        }

        /* ── Form Wrapper ── */
        .bay-form-wrapper {
            max-width: 960px;
            margin: 0 auto;
        }

        .bay-form-wrapper .fi-fo-field-wrp-label label {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            color: #334155 !important;
        }

        .dark .bay-form-wrapper .fi-fo-field-wrp-label label {
            color: #cbd5e1 !important;
        }

        .bay-form-wrapper .fi-input-wrp,
        .bay-form-wrapper .fi-select-input {
            border-radius: 0.625rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        /* Form Actions Row */
        .bay-form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.875rem;
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
        }

        .dark .bay-form-actions {
            border-top-color: #334155;
        }

        .bay-btn-save {
            height: 2.625rem;
            padding: 0 1.5rem;
            background: #004799;
            color: #ffffff !important;
            font-size: 0.875rem;
            font-weight: 700;
            border: none;
            border-radius: 0.625rem;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 6px rgba(0, 71, 153, 0.25);
        }

        .bay-btn-save:hover {
            background: #003370;
        }

        .bay-btn-cancel {
            height: 2.625rem;
            padding: 0 1.25rem;
            background: #ffffff;
            color: #475569 !important;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            border-radius: 0.625rem;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bay-btn-cancel:hover {
            background: #f8fafc;
            color: #0f172a !important;
            border-color: #cbd5e1;
        }

        .dark .bay-btn-cancel {
            background: #0f172a;
            border-color: #334155;
            color: #cbd5e1 !important;
        }

        .dark .bay-btn-cancel:hover {
            background: #1e293b;
            color: #f8fafc !important;
        }
    </style>

    <div class="bay-app">
        <div class="bay-form-wrapper">
            <form wire:submit="create">
                <div class="bay-card">
                    <div class="bay-card-header">
                        <div class="bay-icon-title-group">
                            <div>
                                <h2 class="bay-card-title">Tambah Bidang / Unit Kerja</h2>
                                <p class="bay-card-subtitle">Masukkan nama bidang atau unit kerja baru beserta deskripsi tugasnya</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Schema Fields -->
                    {{ $this->form }}

                    <!-- Form Actions -->
                    <div class="bay-form-actions">
                        <a href="{{ route('filament.admin.resources.divisions.index') }}" class="bay-btn-cancel">
                            Batal
                        </a>

                        <button type="submit" class="bay-btn-save">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Buat Bidang</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
