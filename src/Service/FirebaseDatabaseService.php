<?php

namespace App\Service;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseDatabaseService
{
    private Database $database;

    public function __construct()
    {
        $credentialsPath = dirname(__DIR__, 2) . '/config/firebase/serviceAccountKey.json';
        $databaseUrl = $_SERVER['FIREBASE_DATABASE_URL'] 
            ?? getenv('FIREBASE_DATABASE_URL');

        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri($databaseUrl);
        
        $this->database = $factory->createDatabase();
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }
}