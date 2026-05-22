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
            $decoded = json_decode($credentialsJson, true);

            if (!$decoded) {
                throw new \RuntimeException('FIREBASE_CREDENTIALS_JSON is set but contains invalid JSON');
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