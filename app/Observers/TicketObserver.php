<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\FonnteService;

class TicketObserver
{
    public function __construct(protected FonnteService $fonnte) {}

    /**
     * Tiket baru dibuat → notifikasi ke semua Admin.
     */
    public function created(Ticket $ticket): void
    {
        $ticket->loadMissing(['client.user', 'client.division']);

        $adminUsers = User::role('admin')->whereNotNull('phone')->get();

        foreach ($adminUsers as $admin) {
            $this->fonnte->send(
                $ticket->id,
                $admin->id,
                $admin->phone,
                "📩 *Tiket Baru Masuk*\n".
                "Dari: {$ticket->client->user->name}\n".
                "Bidang: {$ticket->client->division->name}\n".
                "Subjek: {$ticket->subject}\n".
                'Prioritas: '.strtoupper($ticket->priority)."\n\n".
                "Silakan login untuk menangani:\n".
                url('/admin/tickets/'.$ticket->id)
            );
        }
    }

    /**
     * Tiket diupdate → cek perubahan status dan support_id.
     */
    public function updated(Ticket $ticket): void
    {
        $ticket->loadMissing(['client.user', 'support.user']);

        $pegawaiPhone = $ticket->client->user->phone ?? null;
        $pegawaiId = $ticket->client->user->id;

        // Notifikasi ke pegawai saat status berubah
        if ($ticket->isDirty('status') && $pegawaiPhone) {
            $statusLabel = match ($ticket->status) {
                'in_progress' => '🔄 Sedang Diproses',
                'resolved' => '✅ Telah Diselesaikan',
                'closed' => '🔒 Ditutup',
                default => $ticket->status,
            };

            $pesan = "🔔 *Update Tiket #{$ticket->id}*\n".
                     "Subjek: {$ticket->subject}\n".
                     "Status: {$statusLabel}";

            if ($ticket->status === 'resolved') {
                $pesan .= "\n\nTerima kasih telah menggunakan layanan IT Helpdesk\nDinas Pendidikan Provinsi Aceh. 🙏";
            }

            $this->fonnte->send($ticket->id, $pegawaiId, $pegawaiPhone, $pesan);
        }

        // Notifikasi ke IT Support yang baru di-assign
        if ($ticket->isDirty('support_id') && $ticket->support_id) {
            $supportPhone = $ticket->support->user->phone ?? null;
            $supportId = $ticket->support->user->id;

            if ($supportPhone) {
                $this->fonnte->send(
                    $ticket->id,
                    $supportId,
                    $supportPhone,
                    "🛠️ *Tiket Ditugaskan ke Anda*\n".
                    "Subjek: {$ticket->subject}\n".
                    'Prioritas: '.strtoupper($ticket->priority)."\n".
                    "Dari: {$ticket->client->user->name}\n\n".
                    "Segera login dan tangani:\n".
                    url('/admin/tickets/'.$ticket->id)
                );
            }

            // Notifikasi ke pegawai bahwa tiketnya sudah diambil
            if ($pegawaiPhone && ! $ticket->isDirty('status')) {
                $this->fonnte->send(
                    $ticket->id,
                    $pegawaiId,
                    $pegawaiPhone,
                    "🔔 *Tiket Anda Sedang Diproses*\n".
                    "Subjek: {$ticket->subject}\n".
                    "Ditangani oleh: {$ticket->support->user->name}\n".
                    'Status: In Progress'
                );
            }
        }
    }
}
