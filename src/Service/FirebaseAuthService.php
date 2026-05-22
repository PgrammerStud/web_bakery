<?php

namespace App\Service;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\Token\ExpiredToken;
use Psr\Log\LoggerInterface;

class FirebaseAuthService
{
    private Auth $auth;

    public function __construct(
        private readonly string $credentialsPath,
        private readonly LoggerInterface $logger,
    ) {
        $factory = new Factory();

        // Prefer env var (Railway) over file (local dev)
        $credentialsJson = $_ENV['FIREBASE_CREDENTIALS_JSON'] ?? getenv('FIREBASE_CREDENTIALS_JSON');

        if ($credentialsJson) {
    $this->logger->info('Loading Firebase credentials from environment variable');

    // Railway sometimes escapes the JSON — try to fix common issues
    $decoded = json_decode($credentialsJson, true);

    if (!$decoded) {
        // Try stripping surrounding quotes if Railway wrapped it
        $credentialsJson = trim($credentialsJson, '"\'');
        $decoded = json_decode($credentialsJson, true);
    }

    if (!$decoded) {
        throw new \RuntimeException('FIREBASE_CREDENTIALS_JSON contains invalid JSON. Error: ' . json_last_error_msg());
    }

    // ← ADD THIS TEMPORARILY
    $this->logger->info('Firebase creds check', [
    'project_id'  => $decoded['project_id'] ?? 'MISSING',
    'client_email'=> $decoded['client_email'] ?? 'MISSING',
    'key_start'   => substr($decoded['private_key'] ?? '', 0, 40),
    'key_has_real_newlines' => str_contains($decoded['private_key'] ?? '', "\n") ? 'YES' : 'NO (needs str_replace)',
]);

    // Fix private_key if newlines got double-escaped
    if (isset($decoded['private_key'])) {
        $decoded['private_key'] = str_replace('\\n', "\n", $decoded['private_key']);
    }

    $factory = $factory->withServiceAccount($decoded);

        } elseif (file_exists($this->credentialsPath)) {
            $this->logger->info('Loading Firebase credentials from file: ' . $this->credentialsPath);
            $factory = $factory->withServiceAccount($this->credentialsPath);

        } else {
            throw new \RuntimeException(
                'No Firebase credentials found. Set FIREBASE_CREDENTIALS_JSON env var or provide a valid file path.'
            );
        }

        $this->auth = $factory->createAuth();
    }

    public function verifyToken(string $idToken): ?array
    {
        try {
            $this->logger->info('Attempting to verify Firebase ID token');

            $verifiedIdToken = $this->auth->verifyIdToken($idToken);

            $this->logger->info('Firebase ID token verified successfully', [
                'uid'   => $verifiedIdToken->claims()->get('sub'),
                'email' => $verifiedIdToken->claims()->get('email'),
            ]);

            return [
                'uid'   => $verifiedIdToken->claims()->get('sub'),
                'email' => $verifiedIdToken->claims()->get('email'),
                'name'  => $verifiedIdToken->claims()->get('name') ?? null,
                'photo' => $verifiedIdToken->claims()->get('picture') ?? null,
            ];

        } catch (ExpiredToken $e) {
            $this->logger->warning('Firebase ID token is expired', [
                'error' => $e->getMessage(),
            ]);
            return null;

        } catch (\Exception $e) {
            $this->logger->error('Failed to verify Firebase ID token', [
                'error'     => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return null;
        }
    }
}