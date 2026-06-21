# CLAUDE.md — E-Helpdesk Support System
## Dinas Pendidikan Provinsi Aceh

> Panduan konteks lengkap untuk pengembangan skripsi.
> Baca file ini sebelum menulis kode apapun.

---

## Identitas Proyek

| Atribut | Detail |
|---|---|
| **Nama Sistem** | E-Helpdesk Support System |
| **Objek Penelitian** | Dinas Pendidikan Provinsi Aceh |
| **Jenis** | Skripsi / Tugas Akhir |
| **Referensi Jurnal** | Mauliana, Wiguna, Permana (Jurnal Responsif Vol.2 No.1, 2020) |
| **Metode Pengembangan** | Waterfall |
| **Pengujian** | Black Box Testing |

---

## Tech Stack (Terbaru — 2026)

| Layer | Teknologi | Versi |
|---|---|---|
| Language | PHP | 8.3+ |
| Framework | Laravel | 13.x (rilis 17 Maret 2026) |
| Admin Panel | Filament | v5.x (rilis 16 Januari 2026) |
| Reactive UI | Livewire | v4.x |
| CSS Utility | Tailwind CSS | v4.x |
| Role & Permission | Spatie Laravel Permission | ^6.x |
| Database | MySQL | 8.x |
| Realtime Notification | Fonnte (WhatsApp API) | REST API |
| PDF Export | barryvdh/laravel-dompdf | ^3.x |
| Queue / Job | Laravel Queue | (database driver) |

> **Catatan penting:** Filament v5 mensyaratkan Livewire v4 dan Tailwind v4.
> Laravel 13 mensyaratkan PHP minimum 8.3.

---

## Konteks Objek Penelitian

**Dinas Pendidikan Provinsi Aceh** adalah instansi pemerintah daerah yang mengelola
urusan pendidikan di tingkat provinsi, membawahi unit-unit kerja (bidang/seksi) dengan
banyak pegawai yang menggunakan perangkat komputer dan sistem informasi internal.

**Permasalahan yang diadopsi dari jurnal referensi (disesuaikan konteks):**
- Pegawai kesulitan melaporkan gangguan teknis perangkat kerja (komputer, printer, jaringan, aplikasi)
- Tidak ada sistem formal untuk mencatat dan melacak laporan teknis
- Tim IT Support kesulitan memprioritaskan dan mendistribusikan penanganan
- Pimpinan / admin tidak dapat memantau progres penanganan secara real-time
- Tidak ada rekap laporan historis per periode

**Upgrade dibanding jurnal asli:**
- Stack modern: Laravel 13 + Filament v5 (vs PHP native)
- Notifikasi realtime via **Fonnte WhatsApp API** — fitur baru tidak ada di jurnal
- Role management via Spatie (vs session manual)
- Panel admin profesional via Filament Resources

---

## Tiga Role Sistem

### 1. `pegawai` — Pegawai / User Client
Pegawai Dinas Pendidikan Provinsi Aceh yang menggunakan perangkat IT.

**Akses:** Filament Panel (akses terbatas hanya ke tiket miliknya sendiri, diatur via Policy/Otorisasi)

**Fitur:**
- Login ke panel Filament
- Membuat tiket keluhan baru (subjek, bidang/unit kerja, deskripsi, prioritas, lampiran file)
- Melihat daftar tiket milik sendiri beserta status terkini
- Membalas thread tiket (diskusi dengan IT Support) melalui Halaman/Infolist Filament
- Menerima notifikasi WhatsApp otomatis saat status tiket berubah
- Logout

---

### 2. `it_support` — IT Support (Teknisi)
Staf teknis IT di Dinas Pendidikan Provinsi Aceh.

**Akses:** Filament Panel (akses terbatas hanya ke tiket yang di-assign)

**Fitur:**
- Login ke panel Filament
- Melihat daftar tiket yang di-assign kepadanya
- Mengupdate status tiket: `open` → `in_progress` → `resolved`
- Membalas thread tiket (memberikan solusi / update ke pegawai) melalui Halaman/Infolist Filament
- Melihat detail data pegawai dan bidang/unit kerja terkait tiket
- Menerima notifikasi WhatsApp saat tiket baru di-assign
- Logout

---

### 3. `admin` — Admin / Service Desk
Administrator sistem, setara dengan Service Desk / Kepala IT di instansi.

**Akses:** Filament Panel (akses penuh ke semua resource)

**Fitur:**
- Login ke panel Filament
- Dashboard: statistik tiket (total, open, in progress, resolved, closed)
- Mengelola semua tiket: assign ke IT Support, ubah status, hapus
- Mengelola data Pegawai (CRUD, reset password, atur bidang)
- Mengelola data IT Support (CRUD, hak akses)
- Mengelola data Bidang/Unit Kerja (CRUD)
- Generate & export laporan tiket (filter tanggal, bidang, status, IT Support)
- Cetak laporan PDF
- Menerima notifikasi WhatsApp saat tiket baru masuk
- Logout

---

## Struktur Database

```sql
-- Hak akses role
roles: id, name, guard_name, created_at, updated_at
model_has_roles: role_id, model_type, model_id
permissions: (dari Spatie)

-- Pengguna sistem (semua role)
users
  id, name, email, email_verified_at, password,
  phone,        -- nomor HP format 628xxx (untuk Fonnte WA)
  nip,          -- Nomor Induk Pegawai (khusus instansi pemerintah)
  remember_token, created_at, updated_at

-- Bidang / Unit Kerja di Dinas Pendidikan
divisions
  id, name, description, created_at, updated_at

-- Data tambahan pegawai
clients
  id, user_id (FK users), division_id (FK divisions),
  position,     -- jabatan
  created_at, updated_at

-- Data tambahan IT Support
supports
  id, user_id (FK users), division_id (FK divisions),
  position,
  created_at, updated_at

-- Tiket keluhan
tickets
  id,
  client_id (FK clients),
  support_id (FK supports, nullable — diisi saat di-assign),
  subject,
  description (text),
  priority     ENUM('low','medium','high','critical'),
  status       ENUM('open','in_progress','resolved','closed'),
  created_at, updated_at

-- Balasan / thread tiket
replies
  id, ticket_id (FK tickets), user_id (FK users),
  message (text),
  created_at, updated_at

-- Lampiran file (pada tiket maupun balasan)
files
  id,
  ticket_id (FK tickets, nullable),
  reply_id  (FK replies, nullable),
  file_path, file_name, file_size,
  created_at, updated_at

-- Log notifikasi WhatsApp (untuk audit trail)
notification_logs
  id, ticket_id (FK tickets), user_id (FK users),
  phone, message (text),
  status ENUM('sent','failed'),
  sent_at, created_at
```

---

## Alur Sistem Lengkap

```
[PEGAWAI] Login → Buat Tiket Baru
        │
        ▼
[DB] Tiket tersimpan, status: "open"
        │
        ▼
[FONNTE] Kirim WA ke Admin:
  "📩 Tiket Baru — {nama pegawai} / {bidang}
   Subjek: {subject} | Prioritas: {priority}
   Silakan login untuk menangani."
        │
        ▼
[ADMIN] Login Filament → Assign IT Support ke tiket
        │
        ▼
[DB] support_id terisi, status: "in_progress"
        │
        ├──▶ [FONNTE] WA ke IT Support:
        │      "🛠️ Tiket Ditugaskan ke Anda
        │       Subjek: {subject} | Prioritas: {priority}
        │       Segera login dan tangani."
        │
        └──▶ [FONNTE] WA ke Pegawai:
               "🔔 Tiket Anda Sedang Diproses
                Ditangani oleh: {nama IT Support}
                Status: In Progress"
                      │
                      ▼
              [IT SUPPORT] Login → Balas tiket / update status
                      │
                      ▼
              [FONNTE] WA ke Pegawai:
                "💬 Ada Balasan pada Tiket Anda
                 Subjek: {subject}
                 Silakan login untuk melihat detail."
                      │
                      ▼
              [IT SUPPORT] Selesai → Status: "resolved"
                      │
                      ▼
              [FONNTE] WA ke Pegawai:
                "✅ Tiket Anda Telah Diselesaikan
                 Subjek: {subject}
                 Terima kasih telah menggunakan layanan IT Dinas Pendidikan Provinsi Aceh."
                      │
                      ▼
              [ADMIN] Rekap Laporan → Export PDF → Arsip
```

---

## Struktur Direktori Laravel 13

```
app/
├── Filament/
│   ├── Resources/
│   │   ├── TicketResource.php
│   │   │   ├── Pages/
│   │   │   │   ├── ListTickets.php
│   │   │   │   ├── CreateTicket.php
│   │   │   │   ├── EditTicket.php
│   │   │   │   └── ViewTicket.php      ← thread balasan / custom view ada di sini
│   │   ├── UserResource.php            ← kelola pegawai & IT support
│   │   ├── DivisionResource.php        ← kelola bidang/unit kerja
│   │   └── ReportResource.php          ← rekap & export laporan
│   ├── Widgets/
│   │   ├── TicketStatsOverview.php     ← StatsOverviewWidget
│   │   └── LatestTicketsTable.php      ← TableWidget
│   └── Pages/
│       └── Dashboard.php
│
├── Models/
│   ├── User.php
│   ├── Division.php
│   ├── Client.php
│   ├── Support.php
│   ├── Ticket.php
│   ├── Reply.php
│   ├── File.php
│   └── NotificationLog.php
│
├── Services/
│   └── FonnteService.php               ← HTTP client ke Fonnte API
│
├── Observers/
│   ├── TicketObserver.php              ← trigger notifikasi otomatis tiket
│   └── ReplyObserver.php               ← trigger notifikasi otomatis balasan
│
├── Policies/
│   ├── TicketPolicy.php                ← otorisasi per role (Pegawai hanya melihat tiketnya sendiri)
│   ├── UserPolicy.php
│   └── DivisionPolicy.php
│
└── Providers/
    └── AppServiceProvider.php          ← register observer, policy

database/
├── migrations/
│   ├── ..._create_users_table.php
│   ├── ..._create_divisions_table.php
│   ├── ..._create_clients_table.php
│   ├── ..._create_supports_table.php
│   ├── ..._create_tickets_table.php
│   ├── ..._create_replies_table.php
│   ├── ..._create_files_table.php
│   └── ..._create_notification_logs_table.php
└── seeders/
    ├── RolePermissionSeeder.php
    ├── AdminSeeder.php
    ├── DivisionSeeder.php              ← bidang-bidang di Dinas Pendidikan Aceh
    └── DemoUserSeeder.php

resources/
└── views/
    └── reports/
        └── tickets-pdf.blade.php       ← template export PDF laporan
```

---

## Implementasi Fonnte

### `.env`
```env
FONNTE_TOKEN=your_token_here
FONNTE_ENDPOINT=https://api.fonnte.com/send
```

### `app/Services/FonnteService.php`
```php
<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    public function send(int $ticketId, int $userId, string $phone, string $message): void
    {
        $status = 'failed';

        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.token'),
            ])->post(config('services.fonnte.endpoint'), [
                'target'  => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $status = 'sent';
            }
        } catch (\Throwable $e) {
            Log::error('Fonnte error: ' . $e->getMessage());
        }

        // Selalu log, baik berhasil maupun gagal
        NotificationLog::create([
            'ticket_id' => $ticketId,
            'user_id'   => $userId,
            'phone'     => $phone,
            'message'   => $message,
            'status'    => $status,
            'sent_at'   => now(),
        ]);
    }
}
```

### `config/services.php` — tambahkan:
```php
'fonnte' => [
    'token'    => env('FONNTE_TOKEN'),
    'endpoint' => env('FONNTE_ENDPOINT', 'https://api.fonnte.com/send'),
],
```

### `app/Observers/TicketObserver.php`
```php
<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Services\FonnteService;

class TicketObserver
{
    public function __construct(protected FonnteService $fonnte) {}

    // Tiket baru dibuat → notifikasi ke Admin
    public function created(Ticket $ticket): void
    {
        $adminUsers = \App\Models\User::role('admin')->whereNotNull('phone')->get();

        foreach ($adminUsers as $admin) {
            $this->fonnte->send(
                $ticket->id,
                $admin->id,
                $admin->phone,
                "📩 *Tiket Baru Masuk*\n" .
                "Dari: {$ticket->client->user->name}\n" .
                "Bidang: {$ticket->client->division->name}\n" .
                "Subjek: {$ticket->subject}\n" .
                "Prioritas: " . strtoupper($ticket->priority) . "\n\n" .
                "Silakan login untuk menangani:\n" .
                url('/admin/tickets/' . $ticket->id)
            );
        }
    }

    // Tiket diupdate → cek perubahan status dan support_id
    public function updated(Ticket $ticket): void
    {
        $pegawaiPhone = $ticket->client->user->phone ?? null;
        $pegawaiId    = $ticket->client->user->id;

        // Notifikasi ke pegawai saat status berubah
        if ($ticket->isDirty('status') && $pegawaiPhone) {
            $statusLabel = match ($ticket->status) {
                'in_progress' => '🔄 Sedang Diproses',
                'resolved'    => '✅ Telah Diselesaikan',
                'closed'      => '🔒 Ditutup',
                default       => $ticket->status,
            };

            $pesan = "🔔 *Update Tiket #{$ticket->id}*\n" .
                     "Subjek: {$ticket->subject}\n" .
                     "Status: {$statusLabel}";

            if ($ticket->status === 'resolved') {
                $pesan .= "\n\nTerima kasih telah menggunakan layanan IT Helpdesk\nDinas Pendidikan Provinsi Aceh. 🙏";
            }

            $this->fonnte->send($ticket->id, $pegawaiId, $pegawaiPhone, $pesan);
        }

        // Notifikasi ke IT Support yang baru di-assign
        if ($ticket->isDirty('support_id') && $ticket->support_id) {
            $supportPhone = $ticket->support->user->phone ?? null;
            $supportId    = $ticket->support->user->id;

            if ($supportPhone) {
                $this->fonnte->send(
                    $ticket->id,
                    $supportId,
                    $supportPhone,
                    "🛠️ *Tiket Ditugaskan ke Anda*\n" .
                    "Subjek: {$ticket->subject}\n" .
                    "Prioritas: " . strtoupper($ticket->priority) . "\n" .
                    "Dari: {$ticket->client->user->name}\n\n" .
                    "Segera login dan tangani:\n" .
                    url('/admin/tickets/' . $ticket->id)
                );
            }

            // Notifikasi ke pegawai bahwa tiketnya sudah diambil
            if ($pegawaiPhone && !$ticket->isDirty('status')) {
                $this->fonnte->send(
                    $ticket->id,
                    $pegawaiId,
                    $pegawaiPhone,
                    "🔔 *Tiket Anda Sedang Diproses*\n" .
                    "Subjek: {$ticket->subject}\n" .
                    "Ditangani oleh: {$ticket->support->user->name}\n" .
                    "Status: In Progress"
                );
            }
        }
    }
}
```

### `app/Providers/AppServiceProvider.php`
```php
use App\Models\Ticket;
use App\Models\Reply;
use App\Observers\TicketObserver;
use App\Observers\ReplyObserver;

public function boot(): void
{
    Ticket::observe(TicketObserver::class);
    Reply::observe(ReplyObserver::class);
}
```

---

## Notifikasi saat Ada Reply Baru (ReplyObserver)

Kirim notifikasi otomatis menggunakan model observer `ReplyObserver`:

```php
<?php

namespace App\Observers;

use App\Models\Reply;
use App\Services\FonnteService;

class ReplyObserver
{
    public function __construct(protected FonnteService $fonnte) {}

    public function created(Reply $reply): void
    {
        $ticket = $reply->ticket;

        // Jika yang membalas adalah IT Support / Admin, kirim WA ke Pegawai
        if (auth()->user() && auth()->user()->hasAnyRole(['it_support', 'admin'])) {
            $pegawai = $ticket->client->user ?? null;

            if ($pegawai && $pegawai->phone) {
                $this->fonnte->send(
                    $ticket->id,
                    $pegawai->id,
                    $pegawai->phone,
                    "💬 *Ada Balasan pada Tiket Anda*\n" .
                    "Subjek: {$ticket->subject}\n" .
                    "Dari: " . auth()->user()->name . "\n\n" .
                    "Login untuk melihat detail:\n" .
                    url('/admin/tickets/' . $ticket->id)
                );
            }
        }

        // Jika yang membalas adalah Pegawai, kirim WA ke IT Support
        if (auth()->user() && auth()->user()->hasRole('pegawai')) {
            $support = $ticket->support->user ?? null;

            if ($support && $support->phone) {
                $this->fonnte->send(
                    $ticket->id,
                    $support->id,
                    $support->phone,
                    "💬 *Ada Balasan dari Pegawai*\n" .
                    "Subjek: {$ticket->subject}\n" .
                    "Dari: " . auth()->user()->name . "\n\n" .
                    "Login untuk melihat detail:\n" .
                    url('/admin/tickets/' . $ticket->id)
                );
            }
        }
    }
}
```

---

## Filament v5 Resources

### TicketResource — Fitur Utama

```php
// Form fields (Admin create/edit)
Forms\Components\Select::make('client_id')
    ->relationship('client', 'id')
    ->getOptionLabelFromRecordUsing(fn ($r) => $r->user->name . ' — ' . $r->division->name)
    ->searchable()->required(),

Forms\Components\Select::make('support_id')
    ->relationship('support', 'id')
    ->getOptionLabelFromRecordUsing(fn ($r) => $r->user->name)
    ->searchable()->nullable()
    ->label('Assign IT Support'),

Forms\Components\Select::make('priority')
    ->options([
        'low'      => 'Rendah',
        'medium'   => 'Sedang',
        'high'     => 'Tinggi',
        'critical' => 'Kritis',
    ])->required(),

Forms\Components\Select::make('status')
    ->options([
        'open'        => 'Open',
        'in_progress' => 'In Progress',
        'resolved'    => 'Resolved',
        'closed'      => 'Closed',
    ])->required(),

// Table columns
Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
Tables\Columns\TextColumn::make('client.user.name')->label('Pegawai')->searchable(),
Tables\Columns\TextColumn::make('client.division.name')->label('Bidang'),
Tables\Columns\TextColumn::make('subject')->limit(40)->searchable(),
Tables\Columns\BadgeColumn::make('priority')
    ->colors([
        'secondary' => 'low',
        'warning'   => 'medium',
        'danger'    => 'high',
        'primary'   => 'critical',
    ]),
Tables\Columns\BadgeColumn::make('status')
    ->colors([
        'secondary' => 'open',
        'warning'   => 'in_progress',
        'success'   => 'resolved',
        'danger'    => 'closed',
    ]),
Tables\Columns\TextColumn::make('support.user.name')->label('IT Support')->default('Belum diassign'),
Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable(),

// Filters
Tables\Filters\SelectFilter::make('status')
    ->options([...]),
Tables\Filters\SelectFilter::make('priority')
    ->options([...]),
Tables\Filters\Filter::make('created_at')
    ->form([
        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
    ])
```

### Dashboard Widgets

```php
// TicketStatsOverview.php
protected function getStats(): array
{
    return [
        Stat::make('Total Tiket', Ticket::count()),
        Stat::make('Open', Ticket::where('status', 'open')->count())
            ->color('warning'),
        Stat::make('In Progress', Ticket::where('status', 'in_progress')->count())
            ->color('primary'),
        Stat::make('Resolved', Ticket::where('status', 'resolved')->count())
            ->color('success'),
    ];
}
```

---

## Prioritas & Status Tiket

### Prioritas
| Nilai | Label | Badge Filament | Keterangan |
|---|---|---|---|
| `low` | Rendah | `secondary` (abu) | Gangguan minor, tidak urgent |
| `medium` | Sedang | `warning` (kuning) | Mengganggu produktivitas |
| `high` | Tinggi | `danger` (merah) | Berdampak ke pekerjaan utama |
| `critical` | Kritis | `primary` (biru tua) | Sistem lumpuh total |

### Status
| Nilai | Label | Badge Filament | Keterangan |
|---|---|---|---|
| `open` | Terbuka | `secondary` | Baru masuk, belum di-assign |
| `in_progress` | Diproses | `warning` | Sudah di-assign ke IT Support |
| `resolved` | Selesai | `success` | IT Support sudah menyelesaikan |
| `closed` | Ditutup | `danger` | Admin menutup tiket |

---

## Seeder — Bidang Dinas Pendidikan Provinsi Aceh

```php
// database/seeders/DivisionSeeder.php

$divisions = [
    'Sekretariat',
    'Bidang Pembinaan Sekolah Dasar',
    'Bidang Pembinaan Sekolah Menengah Pertama',
    'Bidang Pembinaan Sekolah Menengah Atas',
    'Bidang Pembinaan Sekolah Menengah Kejuruan',
    'Bidang Pembinaan Pendidikan Khusus',
    'Bidang Pembinaan Ketenagaan',
    'Bidang Pengelolaan Keuangan dan Aset',
    'Unit Pelaksana Teknis (UPT)',
    'Bagian Umum dan Kepegawaian',
    'Sub Bagian Perencanaan dan Program',
];
```

---

## Perintah Artisan — Urutan Setup

```bash
# 1. Buat project Laravel 13
composer create-project laravel/laravel helpdesk-disdik

# 2. Install Filament v5
composer require filament/filament:"^5.0"
php artisan filament:install --panels

# 3. Install Spatie Laravel Permission
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 4. Install DomPDF untuk export laporan
composer require barryvdh/laravel-dompdf

# 5. Jalankan semua migration
php artisan migrate

# 6. Jalankan seeder
php artisan db:seed

# 7. Buat Filament Resources
php artisan make:filament-resource Ticket --generate
php artisan make:filament-resource Division --generate
php artisan make:filament-resource User --generate

# 8. Buat Filament Widgets
php artisan make:filament-widget TicketStatsOverview --stats-overview
php artisan make:filament-widget LatestTicketsTable --table

# 9. Buat Service, Observer, Policy
php artisan make:class Services/FonnteService
php artisan make:observer TicketObserver --model=Ticket
php artisan make:observer ReplyObserver --model=Reply
php artisan make:policy TicketPolicy --model=Ticket
php artisan make:policy UserPolicy --model=User
php artisan make:policy DivisionPolicy --model=Division
```

---

## Skenario Black Box Testing

### Role: Pegawai
| No | Skenario | Input | Output yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Login pegawai | Email & password valid | Masuk ke panel Filament | Valid |
| 2 | Login gagal | Password salah | Pesan error, tetap di login | Valid |
| 3 | Buat tiket baru | Subjek, bidang, deskripsi, prioritas | Tiket tersimpan, WA terkirim ke admin | Valid |
| 4 | Lihat daftar tiket | — | Hanya tiket milik sendiri | Valid |
| 5 | Lihat detail tiket | Klik tiket | Detail + thread balasan di Filament | Valid |
| 6 | Balas tiket | Ketik pesan & submit | Reply tersimpan, WA ke IT Support | Valid |
| 7 | Upload lampiran | File saat buat/balas tiket | File tersimpan di storage | Valid |
| 8 | Logout | Klik logout | Kembali ke halaman login | Valid |

### Role: IT Support
| No | Skenario | Input | Output yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Login IT Support | Email & password valid | Masuk ke panel Filament | Valid |
| 2 | Lihat tiket | — | Hanya tiket yang di-assign kepadanya | Valid |
| 3 | Update status | Pilih status baru | Status berubah, WA ke pegawai | Valid |
| 4 | Balas tiket | Ketik pesan & submit | Reply tersimpan, WA ke pegawai | Valid |
| 5 | Lihat data pegawai | Klik detail | Data pegawai & bidang tampil | Valid |
| 6 | Logout | Klik logout | Kembali ke login | Valid |

### Role: Admin
| No | Skenario | Input | Output yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Login Admin | Email & password valid | Masuk ke panel Filament | Valid |
| 2 | Lihat dashboard | — | Statistik tiket tampil | Valid |
| 3 | Assign tiket | Pilih IT Support dari dropdown | support_id tersimpan, WA ke IT Support & pegawai | Valid |
| 4 | Ubah status tiket | Pilih status | Status berubah, WA ke pegawai | Valid |
| 5 | Tambah pegawai | Form data pegawai | User & client tersimpan | Valid |
| 6 | Tambah IT Support | Form data IT support | User & support tersimpan | Valid |
| 7 | Tambah bidang | Nama & deskripsi bidang | Divisi tersimpan | Valid |
| 8 | Hapus tiket | Konfirmasi hapus | Tiket terhapus | Valid |
| 9 | Rekap laporan | Filter tanggal & bidang | Tabel rekap tampil | Valid |
| 10 | Export PDF | Klik export | File PDF terunduh | Valid |
| 11 | Logout | Klik logout | Kembali ke login | Valid |

---

## Spesifikasi Dokumen Sistem (untuk BAB Implementasi Skripsi)

### Dokumen Input — Formulir Tiket Support
| Atribut | Detail |
|---|---|
| Nama Dokumen | Formulir Tiket Support (TS) |
| Fungsi | Mengajukan keluhan teknis |
| Sumber | Pegawai / User Client |
| Tujuan | IT Support |
| Media | Filament Panel (Halaman Pegawai) |
| Jumlah | 1 halaman |
| Frekuensi | Setiap ada keluhan |

### Dokumen Output — Laporan Tiket Support
| Atribut | Detail |
|---|---|
| Nama Dokumen | Laporan Tiket Support (LTS) |
| Fungsi | Merekap laporan penanganan teknis |
| Sumber | Admin / Service Desk |
| Tujuan | Kepala Bidang IT / Pimpinan Dinas |
| Media | File PDF & Kertas |
| Jumlah | 2 rangkap |
| Frekuensi | Setiap bulan / sesuai kebutuhan |

---

## Perbandingan dengan Jurnal Referensi (untuk BAB 1 & BAB 2)

| Aspek | Jurnal Referensi (2020) | Skripsi Ini (2026) |
|---|---|---|
| Framework | PHP Native + HTML/CSS/JS | Laravel 13 (rilis Mar 2026) |
| Admin Panel | Custom HTML manual | Filament v5 (rilis Jan 2026) |
| Reactive UI | jQuery / JS biasa | Livewire v4 |
| CSS | Bootstrap | Tailwind CSS v4 |
| Notifikasi | ❌ Tidak ada | ✅ WhatsApp realtime via Fonnte |
| Role Management | Session manual | Spatie Laravel Permission |
| Objek Penelitian | PT Akur Pratama (retail swasta) | Dinas Pendidikan Provinsi Aceh (instansi pemerintah) |
| Arsitektur | Prosedural | MVC + Service Pattern + Observer |
| Export Laporan | PDF & Kertas | PDF via DomPDF (Laravel) |
| Pengujian | Black Box | Black Box Testing |
| Metode Pengembangan | Waterfall | Waterfall |

---

## Aturan Koding

- Selalu gunakan **Filament Form Schema & Validation Rules** untuk validasi input tiket dan balasan
- Selalu gunakan **Policy** untuk otorisasi akses tiket per role (`TicketPolicy`)
- Semua notifikasi Fonnte **wajib di-log** ke tabel `notification_logs` (untuk audit trail skripsi)
- Nomor HP disimpan format internasional **tanpa `+`** → contoh: `6281234567890`
- Jika Fonnte gagal terkirim → log sebagai `status: failed`, **sistem tetap berjalan normal**
- Gunakan **Filament in-app notification** sebagai backup/tambahan notifikasi internal
- File lampiran disimpan di `storage/app/public/tickets/` dengan `php artisan storage:link`
- Gunakan **soft delete** pada tiket (`SoftDeletes` trait) agar data tidak hilang permanen
- Queue driver gunakan `database` untuk job notifikasi agar tidak memblokir response

---

## Referensi Jurnal

> Mauliana, P., Wiguna, W., & Permana, A. Y. (2020). Pengembangan E-Helpdesk Support System
> Berbasis Web di PT Akur Pratama. *Jurnal Responsif: Riset Sains & Informatika*, 2(1), 19–29.
> E-ISSN: 2685-6964.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3
- filament/filament (FILAMENT) - v5
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
