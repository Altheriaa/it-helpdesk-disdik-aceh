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

**Akses:** Portal Pegawai (halaman Blade, bukan panel Filament)

**Fitur:**
- Login dengan email & password
- Membuat tiket keluhan baru (subjek, bidang/unit kerja, deskripsi, prioritas, lampiran file)
- Melihat daftar tiket milik sendiri beserta status terkini
- Membalas thread tiket (diskusi dengan IT Support)
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
- Membalas thread tiket (memberikan solusi / update ke pegawai)
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
│   │   │   │   └── ViewTicket.php      ← thread balasan ada di sini
│   │   ├── UserResource.php            ← kelola pegawai & IT support
│   │   ├── DivisionResource.php        ← kelola bidang/unit kerja
│   │   └── ReportResource.php          ← rekap & export laporan
│   ├── Widgets/
│   │   ├── TicketStatsOverview.php     ← StatsOverviewWidget
│   │   └── LatestTicketsTable.php      ← TableWidget
│   └── Pages/
│       └── Dashboard.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── PegawaiAuthController.php
│   │   ├── TicketController.php        ← portal pegawai
│   │   └── ReplyController.php
│   ├── Middleware/
│   │   └── EnsureRole.php
│   └── Requests/
│       ├── StoreTicketRequest.php
│       └── StoreReplyRequest.php
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
│   └── TicketObserver.php              ← trigger notifikasi otomatis
│
├── Policies/
│   └── TicketPolicy.php                ← otorisasi per role
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
    ├── layouts/
    │   └── pegawai.blade.php           ← layout portal pegawai
    └── pegawai/
        ├── dashboard.blade.php
        ├── tickets/
        │   ├── index.blade.php
        │   ├── create.blade.php
        │   └── show.blade.php          ← detail + thread
        └── auth/
            └── login.blade.php
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
use App\Observers\TicketObserver;

public function boot(): void
{
    Ticket::observe(TicketObserver::class);
}
```

---

## Notifikasi saat Ada Reply Baru

Kirim notifikasi dari `ReplyController` atau observer `Reply`:

```php
// Setelah reply tersimpan, notifikasi ke pegawai (jika yang reply adalah IT Support / Admin)
if (auth()->user()->hasRole(['it_support', 'admin'])) {
    $ticket  = $reply->ticket;
    $pegawai = $ticket->client->user;

    if ($pegawai->phone) {
        app(FonnteService::class)->send(
            $ticket->id,
            $pegawai->id,
            $pegawai->phone,
            "💬 *Ada Balasan pada Tiket Anda*\n" .
            "Subjek: {$ticket->subject}\n" .
            "Dari: " . auth()->user()->name . "\n\n" .
            "Login untuk melihat detail:\n" .
            url('/tiket/' . $ticket->id)
        );
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
php artisan make:policy TicketPolicy --model=Ticket

# 10. Buat Controller portal pegawai
php artisan make:controller TicketController
php artisan make:controller ReplyController
php artisan make:request StoreTicketRequest
php artisan make:request StoreReplyRequest
```

---

## Skenario Black Box Testing

### Role: Pegawai
| No | Skenario | Input | Output yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Login pegawai | Email & password valid | Masuk ke dashboard pegawai | Valid |
| 2 | Login gagal | Password salah | Pesan error, tetap di login | Valid |
| 3 | Buat tiket baru | Subjek, bidang, deskripsi, prioritas | Tiket tersimpan, WA terkirim ke admin | Valid |
| 4 | Lihat daftar tiket | — | Hanya tiket milik sendiri | Valid |
| 5 | Lihat detail tiket | Klik tiket | Detail + thread balasan | Valid |
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
| Media | Halaman Web (portal pegawai) |
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

- Selalu gunakan **Form Request** untuk validasi input (`StoreTicketRequest`, `StoreReplyRequest`)
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
