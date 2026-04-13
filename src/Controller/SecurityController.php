<?php
// src/Controller/SecurityController.php
namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google')]
    public function connect(ClientRegistry $clientRegistry)
    {
        
        return $clientRegistry
            ->getClient('google')
            ->redirect(
                  [  'openid','email', 'profile']
                //   [
                //      'prompt' => 'select_account'
                //       ]
);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheck()
    {
        // This will be handled by Symfony security
    }
}