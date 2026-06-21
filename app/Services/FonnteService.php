<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * Send a WhatsApp message via Fonnte API and log the result.
     */
    public function send(int $ticketId, int $userId, string $phone, string $message): void
    {
        $status = 'failed';

        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.token'),
            ])->timeout(10)->connectTimeout(5)->post(config('services.fonnte.endpoint'), [
                'target' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $status = 'sent';
            }
        } catch (\Throwable $e) {
            Log::error('Fonnte error: '.$e->getMessage());
        }

        // Selalu log, baik berhasil maupun gagal
        NotificationLog::create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'phone' => $phone,
            'message' => $message,
            'status' => $status,
            'sent_at' => now(),
        ]);
    }
}
