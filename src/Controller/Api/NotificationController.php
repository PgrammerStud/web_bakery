<?php

namespace App\Controller\Api;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class NotificationController extends AbstractController
{
   #[Route('/notifications/send', name: 'api_notifications_send', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
public function send(
    Request $request,
    UserRepository $userRepository
): JsonResponse {
    $body = json_decode($request->getContent(), true);

    $topic   = $body['topic']  ?? null;
    $title   = $body['title']  ?? 'Notification';
    $msgBody = $body['body']   ?? '';
    $data    = $body['data']   ?? [];

    if (!$topic) {
        return $this->json(['message' => 'topic is required'], 400);
    }

    try {
        $credentialsPath = $this->getParameter('kernel.project_dir') . '/config/firebase/serviceAccountKey.json';
        $factory         = (new Factory)->withServiceAccount($credentialsPath);
        $messaging       = $factory->createMessaging();
    } catch (\Throwable $e) {
        // Firebase not configured — log and return 200 so the order flow isn't blocked
        return $this->json(['message' => 'Notification service unavailable'], 200);
    }

    /** @var \App\Entity\User $sender */
    $sender = $this->getUser();

    try {
        if ($topic === 'staff') {
            $staffUsers = array_filter(
                $userRepository->findAll(),
                fn($u) => in_array('ROLE_STAFF', $u->getRoles(), true)
                       || in_array('ROLE_ADMIN', $u->getRoles(), true)
            );

            $tokens = array_values(array_filter(
                array_map(fn($u) => $u->getFcmToken(), $staffUsers)
            ));

            if (empty($tokens)) {
                return $this->json(['message' => 'No staff tokens found'], 200);
            }

            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $msgBody))
                ->withData($data);

            $messaging->sendMulticast($message, $tokens);

        } elseif ($topic === 'customer_self') {
            $fcmToken = $sender->getFcmToken();

            if (!$fcmToken) {
                return $this->json(['message' => 'No FCM token for user'], 200);
            }

            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create($title, $msgBody))
                ->withData($data);

            $messaging->send($message);

        } else {
            return $this->json(['message' => 'Unknown topic'], 400);
        }

    } catch (\Throwable $e) {
        // Firebase send failed — never let this crash the caller's order flow
        // Log it properly if you have a logger injected
        return $this->json([
            'message' => 'Notification delivery failed',
            'detail'  => $e->getMessage(), // remove this in production
        ], 200); // ← 200, not 500 — notifications are non-critical
    }

    return $this->json(['message' => 'Notification sent']);
}
}