<?php

namespace App\Service;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseDatabaseService
{
    private Database $database;

    public function __construct()
    {
        $databaseUrl = $_SERVER['FIREBASE_DATABASE_URL'] 
            ?? getenv('FIREBASE_DATABASE_URL');

        // Try env variable first (Railway), fall back to file (local)
        $credentialsJson = $_SERVER['FIREBASE_CREDENTIALS_JSON'] 
            ?? getenv('FIREBASE_CREDENTIALS_JSON');

        if ($credentialsJson) {
            // Railway — read from environment variable
            $credentials = json_decode($credentialsJson, true);
            $factory = (new Factory)
                ->withServiceAccount($credentials)
                ->withDatabaseUri($databaseUrl);
        } else {
            // Local — read from file
            $credentialsPath = dirname(__DIR__, 2) . '/config/firebase/serviceAccountKey.json';
            $factory = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->withDatabaseUri($databaseUrl);
        }

        $this->database = $factory->createDatabase();
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }
}