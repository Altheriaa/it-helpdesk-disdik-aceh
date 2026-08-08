<x-filament-panels::page>
    @php
        $ticket = $this->record;
        $replies = $ticket->replies()->with(['user.roles', 'files'])->get();
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

        $statusColor = [
            'open'        => '#d97706',
            'in_progress' => '#0284c7',
            'resolved'    => '#059669',
            'closed'      => '#64748b',
            'cancelled'   => '#ef4444',
        ];

        $statusBg = [
            'open'        => '#fffbeb',
            'in_progress' => '#f0f9ff',
            'resolved'    => '#ecfdf5',
            'closed'      => '#f8fafc',
            'cancelled'   => '#fef2f2',
        ];

        $statusLabel = [
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
            'cancelled'   => 'Cancelled',
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

        /* ── Grid Layout ── */
        .bay-view-grid {
            display: grid;
            grid-template-columns: 1fr 1.35fr;
            gap: 1.5rem;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .bay-view-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── Card Styling ── */
        .bay-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            position: relative;
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
            padding-bottom: 1rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .dark .bay-card-header {
            border-bottom-color: #334155;
        }

        .bay-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .dark .bay-card-title {
            color: #f8fafc;
        }

        .bay-count-tag {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
        }

        .dark .bay-count-tag {
            color: #94a3b8;
        }

        /* ── Ticket Detail Grid ── */
        .bay-detail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 640px) {
            .bay-detail-grid {
                grid-template-columns: 1fr;
            }
        }

        .bay-detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .bay-detail-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
        }

        .dark .bay-detail-label {
            color: #94a3b8;
        }

        .bay-detail-val {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
        }

        .dark .bay-detail-val {
            color: #f8fafc;
        }

        .bay-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 0.2rem 0.6rem;
            border-radius: 0.375rem;
            width: fit-content;
        }

        /* ── Chat Container ── */
        .bay-chat-thread {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            max-height: 480px;
            overflow-y: auto;
            padding-right: 0.5rem;
            margin-bottom: 1.25rem;
            scroll-behavior: smooth;
        }

        /* Scrollbar */
        .bay-chat-thread::-webkit-scrollbar {
            width: 5px;
        }
        .bay-chat-thread::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }

        /* Message Item Left (User / Pegawai) */
        .bay-msg-left {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            max-width: 88%;
        }

        .bay-msg-right {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            max-width: 88%;
            margin-left: auto;
            flex-direction: row-reverse;
        }

        .bay-msg-avatar {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            background: #e2e8f0;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid #cbd5e1;
        }

        .bay-msg-avatar.officer {
            background: #004799;
            color: #ffffff;
            border-color: #003370;
        }

        .bay-msg-content {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .bay-msg-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
        }

        .bay-msg-author {
            font-weight: 700;
            color: #0f172a;
        }

        .dark .bay-msg-author {
            color: #f8fafc;
        }

        .bay-msg-author.officer {
            color: #004799;
        }

        .dark .bay-msg-author.officer {
            color: #3b82f6;
        }

        .bay-msg-time {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* Speech Bubbles */
        .bay-bubble-user {
            background: #e0ecff;
            color: #1e293b;
            padding: 1rem 1.25rem;
            border-radius: 1rem;
            border-top-left-radius: 0.25rem;
            font-size: 0.875rem;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .dark .bay-bubble-user {
            background: #1e3a8a;
            color: #f8fafc;
        }

        .bay-bubble-officer {
            background: #cce0ff;
            color: #0f172a;
            padding: 1rem 1.25rem;
            border-radius: 1rem;
            border-top-right-radius: 0.25rem;
            font-size: 0.875rem;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .dark .bay-bubble-officer {
            background: #1e293b;
            border: 1px solid #3b82f6;
            color: #f8fafc;
        }

        /* Image Display inside Chat */
        .bay-chat-img {
            max-width: 100%;
            max-height: 260px;
            border-radius: 0.75rem;
            object-fit: cover;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-top: 0.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            display: block;
            transition: transform 0.15s ease;
        }

        .bay-chat-img:hover {
            transform: scale(1.01);
        }

        /* WhatsApp-style Floating Unread Alert Pill */
        .bay-unread-alert {
            position: absolute;
            bottom: 9.5rem;
            left: 50%;
            transform: translateX(-50%);
            background: #004799;
            color: #ffffff !important;
            font-size: 0.8125rem;
            font-weight: 700;
            padding: 0.45rem 1.125rem;
            border-radius: 9999px;
            border: none;
            box-shadow: 0 4px 14px rgba(0, 71, 153, 0.4);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 30;
            transition: all 0.2s ease;
        }

        .bay-unread-alert:hover {
            background: #003370;
            transform: translateX(-50%) translateY(-2px);
        }

        /* Reply Input Box */
        .bay-reply-box {
            border: 1px solid #cbd5e1;
            border-radius: 1rem;
            padding: 1rem;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .dark .bay-reply-box {
            background: #0f172a;
            border-color: #334155;
        }

        .bay-reply-textarea {
            width: 100%;
            border: none;
            outline: none;
            resize: none;
            font-family: inherit;
            font-size: 0.875rem;
            color: #0f172a;
            background: transparent;
        }

        .dark .bay-reply-textarea {
            color: #f8fafc;
        }

        .bay-reply-textarea::placeholder {
            color: #94a3b8;
        }

        .bay-reply-divider {
            border-top: 1px solid #f1f5f9;
            margin: 0.75rem 0 0.5rem 0;
        }

        .dark .bay-reply-divider {
            border-top-color: #334155;
        }

        .bay-reply-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .bay-attach-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .bay-attach-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .dark .bay-attach-btn:hover {
            background: #1e293b;
            color: #f8fafc;
        }

        .bay-btn-send {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            height: 2.5rem;
            padding: 0 1.25rem;
            background: #004799;
            color: #ffffff !important;
            font-size: 0.875rem;
            font-weight: 700;
            border: none;
            border-radius: 0.625rem;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 2px 4px rgba(0, 71, 153, 0.2);
        }

        .bay-btn-send:hover {
            background: #003370;
            transform: translateY(-1px);
        }

        /* File Pill */
        .bay-file-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.3rem 0.625rem;
            border-radius: 0.375rem;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(0, 0, 0, 0.08);
            font-size: 0.75rem;
            color: #1e293b;
            margin-top: 0.375rem;
        }

        .dark .bay-file-pill {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(255, 255, 255, 0.15);
            color: #f8fafc;
        }

        /* Quick Action Inline Buttons */
        .bay-btn-inline-action {
            border: none;
            background: #eff6ff;
            color: #004799;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            border: 1px solid #dbeafe;
            transition: all 0.15s ease;
        }

        .bay-btn-inline-action:hover {
            background: #dbeafe;
        }
    </style>

    <div class="bay-app">
        <div class="bay-view-grid">

            <!-- ── LEFT COLUMN: Detail Tiket ── -->
            <div class="bay-card">
                <div class="bay-card-header">
                    <h2 class="bay-card-title">Detail Tiket</h2>
                    <span class="bay-badge" style="color: {{ $statusColor[$ticket->status] ?? '#64748b' }}; background: {{ $statusBg[$ticket->status] ?? '#f8fafc' }};">
                        {{ $statusLabel[$ticket->status] ?? ucfirst($ticket->status) }}
                    </span>
                </div>

                <div class="bay-detail-grid">
                    <div class="bay-detail-item">
                        <span class="bay-detail-label">No. Tiket</span>
                        <span class="bay-detail-val">#{{ $ticket->ticket_number }}</span>
                    </div>

                    <div class="bay-detail-item">
                        <span class="bay-detail-label">Pegawai</span>
                        <span class="bay-detail-val">{{ $ticket->client->user->name ?? '—' }}</span>
                    </div>

                    <div class="bay-detail-item">
                        <span class="bay-detail-label">Bidang</span>
                        <span class="bay-detail-val">{{ $ticket->client->division->name ?? '—' }}</span>
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <div class="bay-detail-label" style="margin-bottom: 0.25rem;">Subjek</div>
                    <div class="bay-detail-val" style="font-size: 1rem;">{{ $ticket->subject }}</div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <div class="bay-detail-label" style="margin-bottom: 0.25rem;">Deskripsi</div>
                    <div style="font-size: 0.875rem; color: #334155; line-height: 1.6; white-space: pre-line;">{{ $ticket->description }}</div>
                </div>

                <div class="bay-detail-grid">
                    <div class="bay-detail-item">
                        <span class="bay-detail-label">Prioritas</span>
                        <span class="bay-badge" style="color: {{ $priorityColor[$ticket->priority] ?? '#64748b' }}; background: {{ $priorityBg[$ticket->priority] ?? '#f8fafc' }};">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>

                    <div class="bay-detail-item">
                        <span class="bay-detail-label">Status</span>
                        <div style="display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap;">
                            <span class="bay-detail-val">{{ $statusLabel[$ticket->status] ?? ucfirst($ticket->status) }}</span>
                            @if($user?->hasRole('admin') || ($user?->hasRole('it_support') && in_array($ticket->status, ['open', 'in_progress'])))
                                <button type="button" wire:click="mountAction('changeStatus')" class="bay-btn-inline-action" style="background: #fff7ed; color: #ea580c; border-color: #ffedd5;">
                                    Ubah
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="bay-detail-item">
                        <span class="bay-detail-label">IT Support</span>
                        <div style="display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap;">
                            <span class="bay-detail-val">{{ $ticket->support->user->name ?? 'Belum diassign' }}</span>
                            @if($user?->hasRole('admin'))
                                <button type="button" wire:click="mountAction('assignSupport')" class="bay-btn-inline-action">
                                    Assign
                                </button>
                            @elseif($user?->hasRole('it_support') && $ticket->support_id !== $user->support?->id && ! in_array($ticket->status, ['closed', 'cancelled']))
                                <button type="button" wire:click="mountAction('assignToMe')" class="bay-btn-inline-action" style="background: #ecfdf5; color: #059669; border-color: #a7f3d0;">
                                    Assign ke Saya
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div style="margin-top: 0.5rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                    <span class="bay-detail-label">Dibuat Pada: </span>
                    <span style="font-size: 0.8125rem; font-weight: 600; color: #64748b;">{{ $ticket->created_at->format('d M Y H:i') }}</span>
                </div>

                <!-- Lampiran Tiket jika ada -->
                @if($ticket->files->count() > 0)
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                        <div class="bay-detail-label" style="margin-bottom: 0.5rem;">Lampiran File Tiket</div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @foreach($ticket->files as $file)
                                @php
                                    $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                @endphp

                                @if($isImg)
                                    <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" style="display: inline-block;">
                                        <img src="{{ asset('storage/'.$file->file_path) }}" alt="{{ $file->file_name }}" class="bay-chat-img" />
                                    </a>
                                @else
                                    <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="bay-file-pill">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <span>{{ $file->file_name }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- ── RIGHT COLUMN: Chat / Riwayat Komunikasi ── -->
            <div class="bay-card"
                 x-data="{
                    unreadCount: 0,
                    lastReplyCount: {{ $replies->count() }},
                    userScrolledUp: false,
                    scrollToBottom() {
                        const el = this.$refs.chatThread;
                        if (el) {
                            el.scrollTop = el.scrollHeight;
                            this.unreadCount = 0;
                            this.userScrolledUp = false;
                        }
                    },
                    checkScroll() {
                        const el = this.$refs.chatThread;
                        if (!el) return;
                        const isAtBottom = (el.scrollHeight - el.scrollTop - el.clientHeight) < 60;
                        if (isAtBottom) {
                            this.unreadCount = 0;
                            this.userScrolledUp = false;
                        } else {
                            this.userScrolledUp = true;
                        }
                    },
                    onPoll(currentCount) {
                        if (currentCount > this.lastReplyCount) {
                            const diff = currentCount - this.lastReplyCount;
                            this.lastReplyCount = currentCount;
                            if (this.userScrolledUp) {
                                this.unreadCount += diff;
                            } else {
                                this.$nextTick(() => this.scrollToBottom());
                            }
                        }
                    }
                 }"
                 x-init="$nextTick(() => scrollToBottom())">

                <div class="bay-card-header">
                    <h2 class="bay-card-title">Riwayat Komunikasi</h2>
                    <span class="bay-count-tag">{{ $replies->count() + 1 }} Pesan</span>
                </div>

                <!-- Live Auto-Polling (3 Seconds) for Realtime Updates without reload -->
                <div class="bay-chat-thread"
                     x-ref="chatThread"
                     @scroll="checkScroll()"
                     wire:poll.3s>

                    <!-- Tracker listener for Alpine -->
                    <span x-init="onPoll({{ $replies->count() }})"></span>

                    <!-- Pesan Awal / Deskripsi Tiket oleh Pegawai -->
                    <div class="bay-msg-left">
                        <div class="bay-msg-avatar">
                            {{ strtoupper(substr($ticket->client->user->name ?? 'P', 0, 1)) }}
                        </div>
                        <div class="bay-msg-content">
                            <div class="bay-msg-header">
                                <span class="bay-msg-author">{{ $ticket->client->user->name ?? 'Pegawai' }} (User)</span>
                                <span class="bay-msg-time">{{ $ticket->created_at->format('H:i') }}</span>
                            </div>
                            <div class="bay-bubble-user">
                                {{ $ticket->description }}
                            </div>
                        </div>
                    </div>

                    <!-- Thread Balasan -->
                    @foreach($replies as $reply)
                        @php
                            $isOfficer = $reply->user->hasAnyRole(['admin', 'it_support']);
                        @endphp

                        @if($isOfficer)
                            <!-- IT Officer Message (Right Aligned) -->
                            <div class="bay-msg-right">
                                <div class="bay-msg-avatar officer">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div class="bay-msg-content" style="align-items: flex-end;">
                                    <div class="bay-msg-header">
                                        <span class="bay-msg-time">{{ $reply->created_at->format('H:i') }}</span>
                                        <span class="bay-msg-author officer">{{ $reply->user->name }} (IT Officer)</span>
                                    </div>
                                    <div class="bay-bubble-officer">
                                        {{ $reply->message }}

                                        @if($reply->files->count() > 0)
                                            <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.375rem;">
                                                @foreach($reply->files as $file)
                                                    @php
                                                        $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                                    @endphp

                                                    @if($isImg)
                                                        <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" style="display: block;">
                                                            <img src="{{ asset('storage/'.$file->file_path) }}" alt="{{ $file->file_name }}" class="bay-chat-img" />
                                                        </a>
                                                    @else
                                                        <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="bay-file-pill">
                                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                            <span>{{ $file->file_name }}</span>
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- User Message (Left Aligned) -->
                            <div class="bay-msg-left">
                                <div class="bay-msg-avatar">
                                    {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="bay-msg-content">
                                    <div class="bay-msg-header">
                                        <span class="bay-msg-author">{{ $reply->user->name }} (User)</span>
                                        <span class="bay-msg-time">{{ $reply->created_at->format('H:i') }}</span>
                                    </div>
                                    <div class="bay-bubble-user">
                                        {{ $reply->message }}

                                        @if($reply->files->count() > 0)
                                            <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.375rem;">
                                                @foreach($reply->files as $file)
                                                    @php
                                                        $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                                    @endphp

                                                    @if($isImg)
                                                        <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" style="display: block;">
                                                            <img src="{{ asset('storage/'.$file->file_path) }}" alt="{{ $file->file_name }}" class="bay-chat-img" />
                                                        </a>
                                                    @else
                                                        <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="bay-file-pill">
                                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                            <span>{{ $file->file_name }}</span>
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- ── WhatsApp-style Floating Unread Alert Pill ── -->
                <button type="button"
                        x-show="unreadCount > 0"
                        x-transition
                        @click="scrollToBottom()"
                        class="bay-unread-alert">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                    <span x-text="unreadCount + ' Pesan Baru'"></span>
                </button>

                <!-- ── Bottom Input Box ── -->
                @if(!in_array($ticket->status, ['resolved', 'closed', 'cancelled']))
                    <form wire:submit.prevent="sendReply">
                        <div class="bay-reply-box">
                            <textarea wire:model="replyMessage"
                                      class="bay-reply-textarea"
                                      rows="3"
                                      placeholder="Tulis update atau balasan di sini..."></textarea>

                            <!-- Attachment Previews dengan Gambar Thumbnail sebelum dikirim -->
                            @if(count($attachments) > 0)
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                                    @foreach($attachments as $idx => $file)
                                        @php
                                            $isImg = str_starts_with($file->getMimeType() ?? '', 'image/');
                                        @endphp

                                        @if($isImg)
                                            <div style="position: relative; display: inline-block;">
                                                <img src="{{ $file->temporaryUrl() }}" style="width: 64px; height: 64px; object-fit: cover; border-radius: 0.5rem; border: 1px solid #cbd5e1;" />
                                                <button type="button" wire:click="removeAttachment({{ $idx }})" style="position: absolute; top: -6px; right: -6px; background: #ef4444; color: #fff; border: none; border-radius: 9999px; width: 18px; height: 18px; font-size: 10px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">✕</button>
                                            </div>
                                        @else
                                            <div class="bay-file-pill" style="background: #f1f5f9;">
                                                <span>{{ $file->getClientOriginalName() }}</span>
                                                <button type="button" wire:click="removeAttachment({{ $idx }})" style="border: none; background: none; cursor: pointer; color: #ef4444;">✕</button>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <div class="bay-reply-divider"></div>

                            <div class="bay-reply-foot">
                                <div style="display: flex; gap: 0.25rem; align-items: center;">
                                    <label class="bay-attach-btn" title="Lampirkan File / Gambar">
                                        <input type="file" wire:model="attachments" multiple accept="image/*,application/pdf,.doc,.docx" style="display: none;">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                    </label>
                                </div>

                                <button type="submit" class="bay-btn-send" @click="$nextTick(() => scrollToBottom())">
                                    <span>Kirim Pesan</span>
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div style="padding: 0.875rem; text-align: center; font-size: 0.8125rem; color: #64748b; background: #f8fafc; border-radius: 0.75rem; border: 1px solid #e2e8f0;">
                        Tiket ini telah berstatus <strong>{{ $statusLabel[$ticket->status] }}</strong> dan percakapan ditutup.
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-filament-panels::page>
