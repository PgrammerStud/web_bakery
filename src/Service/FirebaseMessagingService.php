<?php

namespace App\Service;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Contract\Messaging;

class FirebaseMessagingService
{
    private ?Messaging $messaging = null;

    public function __construct(private string $projectDir)
    {
        try {
            $credentialsJson = $_ENV['FIREBASE_CREDENTIALS_JSON'] ?? null;

            if ($credentialsJson) {
                $factory = (new Factory)->withServiceAccount(json_decode($credentialsJson, true));
            } elseif (file_exists($this->projectDir . '/config/firebase/serviceAccountKey.json')) {
                $factory = (new Factory)->withServiceAccount(
                    $this->projectDir . '/config/firebase/serviceAccountKey.json'
                );
            } else {
                return; // Firebase not configured
            }

            $this->messaging = $factory->createMessaging();
        } catch (\Throwable $e) {
            // Firebase not available
        }
    }

    public function isAvailable(): bool
    {
        return $this->messaging !== null;
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        if (!$this->isAvailable() || empty($tokens)) return;

        try {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->sendMulticast($message, $tokens);
        } catch (\Throwable $e) {
            // Non-critical
        }
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): void
    {
        if (!$this->isAvailable()) return;

        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->send($message);
        } catch (\Throwable $e) {
            // Non-critical
        }
    }
}