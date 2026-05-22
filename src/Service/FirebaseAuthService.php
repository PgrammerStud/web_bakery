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

        $credentialsJson = $_ENV['FIREBASE_CREDENTIALS_JSON'] ?? getenv('FIREBASE_CREDENTIALS_JSON');

        if ($credentialsJson) {
            $this->logger->info('Loading Firebase credentials from environment variable');

            // Strip surrounding quotes if Railway wrapped it
            $credentialsJson = trim($credentialsJson, '"\'');

            $decoded = json_decode($credentialsJson, true);

            if (!$decoded) {
                // Some platforms double-escape the whole JSON string — unescape and retry
                $credentialsJson = stripslashes($credentialsJson);
                $decoded = json_decode($credentialsJson, true);
            }

            if (!$decoded) {
                throw new \RuntimeException(
                    'FIREBASE_CREDENTIALS_JSON contains invalid JSON. Error: ' . json_last_error_msg()
                );
            }

            // FIX PRIVATE KEY FIRST — before any use of $decoded
            // Railway and many CI/CD platforms double-escape \n → \\n in env vars
            if (isset($decoded['private_key'])) {
                $decoded['private_key'] = str_replace('\\n', "\n", $decoded['private_key']);
            }

            // Validate required fields
            $required = ['project_id', 'client_email', 'private_key', 'type'];
            foreach ($required as $field) {
                if (empty($decoded[$field])) {
                    throw new \RuntimeException("Firebase credentials missing required field: {$field}");
                }
            }

            // Validate the private key looks correct AFTER the fix
            $privateKey = $decoded['private_key'];
            $hasHeader  = str_contains($privateKey, '-----BEGIN RSA PRIVATE KEY-----')
                       || str_contains($privateKey, '-----BEGIN PRIVATE KEY-----');
            $hasRealNewlines = str_contains($privateKey, "\n");

            $this->logger->info('Firebase credentials loaded', [
                'project_id'       => $decoded['project_id'],
                'client_email'     => $decoded['client_email'],
                'key_has_header'   => $hasHeader   ? 'YES' : 'NO — key is malformed',
                'key_has_newlines' => $hasRealNewlines ? 'YES' : 'NO — key may still be broken',
                'key_start'        => substr($privateKey, 0, 60),
            ]);

            if (!$hasHeader || !$hasRealNewlines) {
                throw new \RuntimeException(
                    'Firebase private_key appears malformed after processing. '
                    . 'Check that FIREBASE_CREDENTIALS_JSON is stored as raw JSON (not base64 or re-encoded).'
                );
            }

            $factory = $factory->withServiceAccount($decoded);

        } elseif (file_exists($this->credentialsPath)) {
            $this->logger->info('Loading Firebase credentials from file: ' . $this->credentialsPath);
            $factory = $factory->withServiceAccount($this->credentialsPath);

        } else {
            throw new \RuntimeException(
                'No Firebase credentials found. '
                . 'Set FIREBASE_CREDENTIALS_JSON env var or provide a valid file path.'
            );
        }

        $this->auth = $factory->createAuth();
    }

    public function verifyToken(string $idToken): ?array
    {
        try {
            $this->logger->info('Attempting to verify Firebase ID token');

            $verifiedIdToken = $this->auth->verifyIdToken($idToken);

            $uid   = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');

            $this->logger->info('Firebase ID token verified successfully', [
                'uid'   => $uid,
                'email' => $email,
            ]);

            return [
                'uid'   => $uid,
                'email' => $email,
                'name'  => $verifiedIdToken->claims()->get('name')    ?? null,
                'photo' => $verifiedIdToken->claims()->get('picture') ?? null,
            ];

        } catch (ExpiredToken $e) {
            $this->logger->warning('Firebase ID token is expired', [
                'error' => $e->getMessage(),
            ]);
            return null;

        } catch (\Throwable $e) {
            // Catch Throwable to also catch kreait internal errors (which aren't always \Exception)
            $this->logger->error('Failed to verify Firebase ID token', [
                'error'     => $e->getMessage(),
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}