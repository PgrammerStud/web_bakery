<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $user = $this->getUser();
            
            // Check if user is admin or staff
            if (in_array('ROLE_ADMIN', $user->getRoles(), true) || in_array('ROLE_STAFF', $user->getRoles(), true)) {
                return $this->redirectToRoute('app_dashboard');
            } else {
                // Regular customer - redirect to product index
                return $this->redirectToRoute('app_product_index');
            }
        }

        $error = $authenticationUtils->getLastAuthenticationError();
       
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
