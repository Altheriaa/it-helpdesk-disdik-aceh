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
        $normalizedPhone = $this->normalizePhone($phone);

        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.token'),
            ])->timeout(10)->connectTimeout(5)->post(config('services.fonnte.endpoint'), [
                'target' => $normalizedPhone,
                'message' => $message,
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? false) === true) {
                $status = 'sent';
            } else {
                $status = 'failed';
                Log::warning('Fonnte API returned non-success', [
                    'ticket_id' => $ticketId,
                    'user_id' => $userId,
                    'phone' => $normalizedPhone,
                    'http_status' => $response->status(),
                    'response' => $body,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Fonnte error: '.$e->getMessage(), [
                'ticket_id' => $ticketId,
                'user_id' => $userId,
                'phone' => $normalizedPhone,
            ]);
        }

        // Selalu log, baik berhasil maupun gagal
        NotificationLog::create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'phone' => $normalizedPhone,
            'message' => $message,
            'status' => $status,
            'sent_at' => now(),
        ]);
    }

    /**
     * Normalize phone number to international format (62xxx).
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return $phone;
    }
}
