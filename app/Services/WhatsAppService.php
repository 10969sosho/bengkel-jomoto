<?php

namespace App\Services;

use App\Models\ServiceOrder;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pengirim notifikasi WhatsApp proses servis ke customer.
 * Gateway lokal (WHATSAPP_ENDPOINT) — bila offline / dimatikan, pesan jatuh ke log aplikasi.
 */
class WhatsAppService
{
    /** Endpoint gateway WhatsApp lokal. */
    public function endpoint(): string
    {
        return (string) config('services.whatsapp.endpoint', 'http://localhost:3300/send');
    }

    /** Kirim pesan notifikasi untuk sebuah work order ke nomor WhatsApp customer. */
    public function notify(ServiceOrder $order, string $headline): void
    {
        $phone = $this->normalizePhone($order->customer?->whatsapp ?: $order->customer?->phone);
        $message = $this->compose($order, $headline);

        if ($phone === null) {
            Log::info('WhatsApp: nomor customer kosong, pesan dicatat ke log.', [
                'wo' => $order->wo_number,
                'message' => $message,
            ]);

            return;
        }

        if (! config('services.whatsapp.enabled', true)) {
            Log::info('WhatsApp: gateway dimatikan, pesan dicatat ke log.', [
                'wo' => $order->wo_number,
                'phone' => $phone,
                'message' => $message,
            ]);

            return;
        }

        try {
            $this->gateway()->post($this->endpoint(), [
                'phone' => $phone,
                'message' => $message,
                'number' => $phone,
                'text' => $message,
            ]);
        } catch (Throwable $e) {
            Log::info('WhatsApp: gateway offline, pesan dicatat ke log.', [
                'wo' => $order->wo_number,
                'phone' => $phone,
                'message' => $message,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Susun pesan ramah berisi nomor WO/SPK, nopol motor, dan status terkini. */
    public function compose(ServiceOrder $order, string $headline): string
    {
        $lines = [
            '*Bengkel Jomoto Center* 🏍️',
            $headline,
            '',
            'No. WO: '.$order->wo_number,
        ];

        if ($order->spk_number) {
            $lines[] = 'No. SPK: '.$order->spk_number;
        }

        $vehicle = $order->vehicle;
        if ($vehicle) {
            $lines[] = 'Motor: '.$vehicle->plate_number.' ('.$vehicle->fullName().')';
        }

        $lines[] = 'Status: '.$order->statusLabel();

        $lines[] = '';
        $lines[] = 'Terima kasih, sampai jumpa di Jomoto Center — Kapasari 73, Surabaya.';

        return implode("\n", $lines);
    }

    private function gateway(): PendingRequest
    {
        return Http::connectTimeout(3)->timeout(5);
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return $digits;
    }
}
