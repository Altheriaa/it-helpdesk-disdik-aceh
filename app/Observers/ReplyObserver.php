<?php

namespace App\Observers;

use App\Models\Reply;
use App\Services\FonnteService;

class ReplyObserver
{
    public function __construct(protected FonnteService $fonnte) {}

    /**
     * Reply baru dibuat → notifikasi ke pihak terkait.
     */
    public function created(Reply $reply): void
    {
        $reply->loadMissing(['ticket.client.user', 'ticket.support.user']);
        $ticket = $reply->ticket;

        $currentUser = auth()->user();
        if (! $currentUser) {
            return;
        }

        // Jika yang membalas adalah IT Support / Admin, kirim WA ke Pegawai
        if ($currentUser->hasAnyRole(['it_support', 'admin'])) {
            $pegawai = $ticket->client->user ?? null;

            if ($pegawai && $pegawai->phone) {
                $this->fonnte->send(
                    $ticket->id,
                    $pegawai->id,
                    $pegawai->phone,
                    "💬 *Ada Balasan pada Tiket Anda*\n".
                    "Subjek: {$ticket->subject}\n".
                    "Dari: {$currentUser->name}\n\n".
                    "Login untuk melihat detail:\n".
                    url('/admin/tickets/'.$ticket->id)
                );
            }
        }

        // Jika yang membalas adalah Pegawai, kirim WA ke IT Support
        if ($currentUser->hasRole('pegawai')) {
            $support = $ticket->support->user ?? null;

            if ($support && $support->phone) {
                $this->fonnte->send(
                    $ticket->id,
                    $support->id,
                    $support->phone,
                    "💬 *Ada Balasan dari Pegawai*\n".
                    "Subjek: {$ticket->subject}\n".
                    "Dari: {$currentUser->name}\n\n".
                    "Login untuk melihat detail:\n".
                    url('/admin/tickets/'.$ticket->id)
                );
            }
        }
    }
}
