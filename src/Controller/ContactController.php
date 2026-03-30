<?php

namespace App\Controller;

use App\Service\ContactEmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ContactEmailService $contactEmailService,
        LoggerInterface $logger,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $formSubmitted = false;
        $formSuccess = false;
        $formError = null;
        $formData = [
            'name' => '',
            'email' => '',
            'phone' => '',
            'subject' => '',
            'message' => '',
            'subscribe' => false,
        ];

        if ($request->isMethod('POST')) {
            $formSubmitted = true;
            
            // Get form data
            $formData['name'] = trim($request->get('name', ''));
            $formData['email'] = trim($request->get('email', ''));
            $formData['phone'] = trim($request->get('phone', ''));
            $formData['subject'] = trim($request->get('subject', ''));
            $formData['message'] = trim($request->get('message', ''));
            $formData['subscribe'] = $request->get('subscribe') ? true : false;
            $token = $request->request->get('_token');

            $logger->info('Contact form submitted', [
                'name' => $formData['name'],
                'email' => $formData['email'],
                'subject' => $formData['subject'],
            ]);

            // Validate CSRF token
            if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('contact', $token))) {
                $formError = 'Security token validation failed. Please try again.';
                $logger->warning('CSRF token validation failed for contact form');
            }
            // Validate required fields
            elseif (!$formData['name'] || !$formData['email'] || !$formData['subject'] || !$formData['message']) {
                $formError = 'Please fill in all required fields.';
                $logger->warning('Contact form validation failed - missing fields');
            }
            // Validate email format
            elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
                $formError = 'Please enter a valid email address.';
                $logger->warning('Contact form validation failed - invalid email', ['email' => $formData['email']]);
            }
            // Try to send emails
            else {
                try {
                    $logger->info('Attempting to send contact emails');

                    // Send notification email to admin
                    $contactEmailService->sendContactNotificationEmail(
                        $formData['name'],
                        $formData['email'],
                        $formData['phone'],
                        $formData['subject'],
                        $formData['message'],
                        $formData['subscribe']
                    );

                    $logger->info('Admin notification email sent successfully');

                    // Send confirmation email to user
                    $contactEmailService->sendConfirmationEmail($formData['name'], $formData['email']);

                    $logger->info('User confirmation email sent successfully');

                    $formSuccess = true;
                    // Clear form data on success
                    $formData = [
                        'name' => '',
                        'email' => '',
                        'phone' => '',
                        'subject' => '',
                        'message' => '',
                        'subscribe' => false,
                    ];
                } catch (\Throwable $e) {
                    $formError = 'There was an error sending your message: ' . $e->getMessage();
                    $logger->error('Contact form email error', [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        return $this->render('contact/index.html.twig', [
            'formSubmitted' => $formSubmitted,
            'formSuccess' => $formSuccess,
            'formError' => $formError,
            'formData' => $formData,
        ]);
    }
}
