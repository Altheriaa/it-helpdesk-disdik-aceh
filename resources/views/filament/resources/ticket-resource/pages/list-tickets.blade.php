<x-filament-panels::page>
    @php
        $kanban = $this->getKanbanTickets();
        $user = auth()->user();

        $priorityColor = [
            'critical' => '#ef4444',
            'high'     => '#ea580c',
            'medium'   => '#d97706',
            'low'      => '#64748b',
        ];

        $priorityBg = [
            'critical' => '#fef2f2',
            'high'     => '#fff7ed',
            'medium'   => '#fffbeb',
            'low'      => '#f8fafc',
        ];
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

        .bay-app a {
            text-decoration: none;
            color: inherit;
        }

        /* ── Filter Card ── */
        .bay-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px 0 rgba(0, 0, 0, 0.02);
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

        /* ── Filter Form Grid ── */
        .bay-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            align-items: flex-end;
        }

        @media (max-width: 768px) {
            .bay-filter-grid {
                grid-template-columns: 1fr;
            }
        }

        .bay-field {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .bay-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
        }

        .dark .bay-label {
            color: #cbd5e1;
        }

        .bay-input, .bay-select {
            height: 2.5rem;
            padding: 0 0.875rem;
            font-size: 0.875rem;
            font-family: inherit;
            border: 1px solid #e2e8f0;
            border-radius: 0.625rem;
            background: #ffffff;
            color: #0f172a;
            outline: none;
            transition: all 0.15s ease;
            width: 100%;
        }

        .dark .bay-input, .dark .bay-select {
            background: #0f172a;
            border-color: #334155;
            color: #f8fafc;
        }

        .bay-input:focus, .bay-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .bay-search-wrapper {
            position: relative;
        }

        .bay-search-wrapper input {
            padding-left: 2.25rem;
        }

        .bay-search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .bay-view-switch {
            display: inline-flex;
            background: #f1f5f9;
            border-radius: 0.625rem;
            padding: 0.2rem;
            gap: 0.2rem;
            height: 2.5rem;
            align-items: center;
            width: 100%;
        }

        .dark .bay-view-switch {
            background: #0f172a;
        }

        .bay-switch-btn {
            height: 2.1rem;
            padding: 0 0.875rem;
            font-size: 0.8125rem;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
            flex: 1;
            text-align: center;
        }

        .bay-switch-btn.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .dark .bay-switch-btn.active {
            background: #1e293b;
            color: #f8fafc;
        }

        /* ── Kanban Grid Layout ── */
        .bay-kanban-board {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.25rem;
            align-items: start;
        }

        @media (max-width: 900px) {
            .bay-kanban-board {
                grid-template-columns: 1fr;
            }
        }

        .bay-column {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 1rem;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
            min-height: 400px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }

        .dark .bay-column {
            background: #1e293b;
            border-color: #334155;
        }

        .bay-col-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 0.875rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .dark .bay-col-head {
            border-bottom-color: #334155;
        }

        .bay-col-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dark .bay-col-title {
            color: #f8fafc;
        }

        .bay-badge-count {
            background: #f1f5f9;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.15rem 0.6rem;
            border-radius: 9999px;
        }

        .dark .bay-badge-count {
            background: #0f172a;
            color: #94a3b8;
        }

        /* ── Ticket Card Styling ── */
        .bay-ticket-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            transition: all 0.15s ease;
        }

        .dark .bay-ticket-card {
            background: #0f172a;
            border-color: #334155;
        }

        .bay-ticket-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.06);
            transform: translateY(-1px);
        }

        .bay-ticket-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .bay-priority-tag {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 0.2rem 0.5rem;
            border-radius: 0.375rem;
        }

        .bay-ticket-id {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
        }

        .bay-ticket-subject {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            display: block;
        }

        .dark .bay-ticket-subject {
            color: #f8fafc;
        }

        .bay-ticket-subject:hover {
            color: #2563eb;
        }

        .bay-ticket-info {
            font-size: 0.75rem;
            color: #64748b;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .dark .bay-ticket-info {
            color: #94a3b8;
        }

        .bay-info-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .bay-ticket-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 0.625rem;
            border-top: 1px solid #f1f5f9;
            margin-top: 0.25rem;
        }

        .dark .bay-ticket-foot {
            border-top-color: #334155;
        }

        .bay-user-info {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .bay-avatar {
            width: 1.625rem;
            height: 1.625rem;
            border-radius: 9999px;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.6875rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dark .bay-avatar {
            background: #334155;
            color: #cbd5e1;
        }

        .bay-user-name {
            font-size: 0.75rem;
            font-weight: 600;
            color: #334155;
        }

        .dark .bay-user-name {
            color: #cbd5e1;
        }

        .bay-action-link {
            font-size: 0.75rem;
            font-weight: 700;
            color: #2563eb;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            transition: color 0.15s;
        }

        .bay-action-link:hover {
            color: #1d4ed8;
        }

        .bay-action-assign {
            font-size: 0.75rem;
            font-weight: 700;
            color: #059669;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            transition: color 0.15s;
        }

        .bay-action-assign:hover {
            color: #047857;
        }

        .bay-empty-state {
            padding: 2rem 1rem;
            text-align: center;
            font-size: 0.8125rem;
            color: #94a3b8;
            border: 1px dashed #e2e8f0;
            border-radius: 0.75rem;
        }

        .dark .bay-empty-state {
            border-color: #334155;
        }
    </style>

    <div class="bay-app" wire:poll.5s>
        <!-- ── Filter & Action Card ── -->
        <div class="bay-card">
            <div class="bay-card-header">
                <div class="bay-icon-box">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="bay-header-title">Filter Data Tiket</h2>
                    <p class="bay-header-subtitle">Pilih rentang waktu dan kriteria untuk menampilkan data tiket helpdesk</p>
                </div>
            </div>

            <div class="bay-filter-grid">
                <!-- Search Box -->
                <div class="bay-field">
                    <label class="bay-label">Pencarian Tiket</label>
                    <div class="bay-search-wrapper">
                        <svg class="bay-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="bay-input"
                               placeholder="Cari berdasarkan subjek, ID, atau nama pegawai...">
                    </div>
                </div>

                <!-- Periode Filter -->
                <div class="bay-field">
                    <label class="bay-label">Pilih Periode</label>
                    <select wire:model.live="periodFilter" class="bay-select">
                        <option value="today">Hari Ini</option>
                        <option value="week">7 Hari Terakhir</option>
                        <option value="all">Semua Periode</option>
                    </select>
                </div>

                <!-- Prioritas Filter -->
                <div class="bay-field">
                    <label class="bay-label">Prioritas Tiket</label>
                    <select wire:model.live="priorityFilter" class="bay-select">
                        <option value="">Semua Prioritas</option>
                        <option value="low">Rendah (Low)</option>
                        <option value="medium">Sedang (Medium)</option>
                        <option value="high">Tinggi (High)</option>
                        <option value="critical">Kritis (Critical)</option>
                    </select>
                </div>

                <!-- Unit / Bidang Filter -->
                <div class="bay-field">
                    <label class="bay-label">Bidang / Unit Kerja</label>
                    <select wire:model.live="divisionFilter" class="bay-select">
                        <option value="">Semua Bidang</option>
                        @foreach($this->divisions as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- View Mode Switch -->
                <div class="bay-field">
                    <label class="bay-label">Tampilan</label>
                    <div class="bay-view-switch">
                        <button type="button"
                                wire:click="setMode('kanban')"
                                class="bay-switch-btn {{ $viewMode === 'kanban' ? 'active' : '' }}">
                            Kanban
                        </button>
                        <button type="button"
                                wire:click="setMode('table')"
                                class="bay-switch-btn {{ $viewMode === 'table' ? 'active' : '' }}">
                            Tabel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Kanban View ── -->
        @if($viewMode === 'kanban')
            <div class="bay-kanban-board">

                <!-- COL 1: BARU / OPEN -->
                <div class="bay-column">
                    <div class="bay-col-head">
                        <div class="bay-col-title">
                            Tiket Baru
                            <span class="bay-badge-count">{{ $kanban['open']->count() }}</span>
                        </div>
                    </div>

                    @forelse($kanban['open'] as $ticket)
                        @php
                            $pc = $priorityColor[$ticket->priority] ?? '#64748b';
                            $pb = $priorityBg[$ticket->priority] ?? '#f8fafc';
                        @endphp
                        <div class="bay-ticket-card">
                            <div class="bay-ticket-top">
                                <span class="bay-priority-tag" style="color: {{ $pc }}; background: {{ $pb }};">
                                    {{ strtoupper($ticket->priority) }}
                                </span>
                                <span class="bay-ticket-id">#{{ $ticket->id }}</span>
                            </div>

                            <a href="{{ route('filament.admin.resources.tickets.view', $ticket) }}" class="bay-ticket-subject">
                                {{ $ticket->subject }}
                            </a>

                            <div class="bay-ticket-info">
                                <div class="bay-info-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0H9m3 0h3m-3-4H9m3 0h3"/></svg>
                                    <span>{{ $ticket->client->division->name ?? '—' }}</span>
                                </div>
                                <div class="bay-info-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $ticket->created_at->format('d M Y, H:i') }} ({{ $ticket->created_at->diffForHumans() }})</span>
                                </div>
                                <div class="bay-info-item" style="font-weight: 600;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>IT: {{ $ticket->support->user->name ?? 'Belum di-assign' }}</span>
                                </div>
                            </div>

                            <div class="bay-ticket-foot">
                                <div class="bay-user-info">
                                    <div class="bay-avatar">{{ strtoupper(substr($ticket->client->user->name ?? 'P', 0, 1)) }}</div>
                                    <span class="bay-user-name">{{ $ticket->client->user->name ?? 'Pegawai' }}</span>
                                </div>

                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    @if($user?->hasRole('it_support') && !$ticket->support_id)
                                        <button type="button" wire:click="assignToMe({{ $ticket->id }})" class="bay-action-assign">
                                            Assign ke Saya
                                        </button>
                                    @endif
                                    @if($user?->hasAnyRole(['admin', 'it_support']))
                                        <button type="button" wire:click="moveStatus({{ $ticket->id }}, 'in_progress')" class="bay-action-link">
                                            Kerjakan →
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bay-empty-state">Tidak ada tiket baru pada periode ini.</div>
                    @endforelse
                </div>

                <!-- COL 2: DIPROSES / IN PROGRESS -->
                <div class="bay-column">
                    <div class="bay-col-head">
                        <div class="bay-col-title">
                            Sedang Diproses
                            <span class="bay-badge-count">{{ $kanban['in_progress']->count() }}</span>
                        </div>
                    </div>

                    @forelse($kanban['in_progress'] as $ticket)
                        @php
                            $pc = $priorityColor[$ticket->priority] ?? '#64748b';
                            $pb = $priorityBg[$ticket->priority] ?? '#f8fafc';
                        @endphp
                        <div class="bay-ticket-card">
                            <div class="bay-ticket-top">
                                <span class="bay-priority-tag" style="color: {{ $pc }}; background: {{ $pb }};">
                                    {{ strtoupper($ticket->priority) }}
                                </span>
                                <span class="bay-ticket-id">#{{ $ticket->id }}</span>
                            </div>

                            <a href="{{ route('filament.admin.resources.tickets.view', $ticket) }}" class="bay-ticket-subject">
                                {{ $ticket->subject }}
                            </a>

                            <div class="bay-ticket-info">
                                <div class="bay-info-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0H9m3 0h3m-3-4H9m3 0h3"/></svg>
                                    <span>{{ $ticket->client->division->name ?? '—' }}</span>
                                </div>
                                <div class="bay-info-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $ticket->created_at->format('d M Y, H:i') }} ({{ $ticket->created_at->diffForHumans() }})</span>
                                </div>
                                <div class="bay-info-item" style="font-weight: 600;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>IT: {{ $ticket->support->user->name ?? 'Belum di-assign' }}</span>
                                </div>
                            </div>

                            <div class="bay-ticket-foot">
                                <div class="bay-user-info">
                                    <div class="bay-avatar">{{ strtoupper(substr($ticket->client->user->name ?? 'P', 0, 1)) }}</div>
                                    <span class="bay-user-name">{{ $ticket->client->user->name ?? 'Pegawai' }}</span>
                                </div>

                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    @if($user?->hasRole('it_support') && (!$ticket->support_id || $ticket->support_id !== $user->support?->id))
                                        <button type="button" wire:click="assignToMe({{ $ticket->id }})" class="bay-action-assign">
                                            Assign ke Saya
                                        </button>
                                    @endif
                                    @if($user?->hasAnyRole(['admin', 'it_support']))
                                        <button type="button" wire:click="moveStatus({{ $ticket->id }}, 'resolved')" class="bay-action-assign">
                                            Selesaikan ✓
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bay-empty-state">Belum ada tiket yang sedang diproses.</div>
                    @endforelse
                </div>

                <!-- COL 3: SELESAI / RESOLVED -->
                <div class="bay-column">
                    <div class="bay-col-head">
                        <div class="bay-col-title">
                            Tiket Selesai
                            <span class="bay-badge-count">{{ $kanban['resolved']->count() }}</span>
                        </div>
                    </div>

                    @forelse($kanban['resolved'] as $ticket)
                        @php
                            $pc = $priorityColor[$ticket->priority] ?? '#64748b';
                            $pb = $priorityBg[$ticket->priority] ?? '#f8fafc';
                        @endphp
                        <div class="bay-ticket-card" style="opacity: 0.9;">
                            <div class="bay-ticket-top">
                                <span class="bay-priority-tag" style="color: {{ $pc }}; background: {{ $pb }};">
                                    {{ strtoupper($ticket->priority) }}
                                </span>
                                <span style="font-size: 0.75rem; font-weight: 700; color: #059669;">✓ Selesai</span>
                            </div>

                            <a href="{{ route('filament.admin.resources.tickets.view', $ticket) }}" class="bay-ticket-subject">
                                {{ $ticket->subject }}
                            </a>

                            <div class="bay-ticket-info">
                                <div class="bay-info-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0H9m3 0h3m-3-4H9m3 0h3"/></svg>
                                    <span>{{ $ticket->client->division->name ?? '—' }}</span>
                                </div>
                                <div class="bay-info-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <div class="bay-info-item" style="font-weight: 600;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>IT: {{ $ticket->support->user->name ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="bay-ticket-foot">
                                <div class="bay-user-info">
                                    <div class="bay-avatar">{{ strtoupper(substr($ticket->client->user->name ?? 'P', 0, 1)) }}</div>
                                    <span class="bay-user-name">{{ $ticket->client->user->name ?? 'Pegawai' }}</span>
                                </div>

                                <a href="{{ route('filament.admin.resources.tickets.view', $ticket) }}" class="bay-action-link" style="color: #64748b;">
                                    Detail →
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="bay-empty-state">Belum ada tiket selesai.</div>
                    @endforelse
                </div>

            </div>
        @else
            {{ $this->table }}
        @endif
    </div>
</x-filament-panels::page>