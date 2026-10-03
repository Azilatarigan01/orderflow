<?php

namespace App\Services;

use App\Models\InAppNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification across multiple enterprise channels:
     * 1. In-App Notification (Database table)
     * 2. Simulated Corporate Email Dispatcher (Logged with RFC compliant format)
     * 3. Corporate Webhook Integration (Logged payload for enterprise ERP sync)
     */
    public static function send(User $user, string $title, string $message, ?string $link = null, string $type = 'info'): InAppNotification
    {
        // 1. Channel: In-App Notification
        $inApp = InAppNotification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
            'type'    => $type,
            'is_read' => false,
        ]);

        // 2. Channel: Corporate Email Dispatcher
        Log::channel('single')->info("[MULTI-CHANNEL NOTIFICATION][EMAIL] Destination: {$user->email} ({$user->name}) | Subject: {$title} | Body: {$message} | Link: {$link}");

        // 3. Channel: Corporate Webhook Event
        Log::channel('single')->info("[MULTI-CHANNEL NOTIFICATION][WEBHOOK] Event: '{$type}' | Data: " . json_encode([
            'recipient_id' => $user->id,
            'email'        => $user->email,
            'title'        => $title,
            'type'         => $type,
            'timestamp'    => now()->toIso8601String(),
        ]));

        return $inApp;
    }

    /**
     * Send notification to a collection or array of users
     */
    public static function sendBatch($users, string $title, string $message, ?string $link = null, string $type = 'info'): void
    {
        foreach ($users as $user) {
            self::send($user, $title, $message, $link, $type);
        }
    }
}
