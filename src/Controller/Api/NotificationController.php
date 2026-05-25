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
use App\Service\FirebaseMessagingService;


#[Route('/api')]
class NotificationController extends AbstractController
{
    public function __construct(private FirebaseMessagingService $firebase) {}

    #[Route('/notifications/send', name: 'api_notifications_send', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function send(Request $request, UserRepository $userRepository): JsonResponse
    {
        if (!$this->firebase->isAvailable()) {
            return $this->json(['message' => 'Notification service unavailable'], 200);
        }

        $body    = json_decode($request->getContent(), true);
        $topic   = $body['topic']  ?? null;
        $title   = $body['title']  ?? 'Notification';
        $msgBody = $body['body']   ?? '';
        $data    = $body['data']   ?? [];

        if (!$topic) {
            return $this->json(['message' => 'topic is required'], 400);
        }

        $sender = $this->getUser();

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

            $this->firebase->sendToTokens($tokens, $title, $msgBody, $data);

        } elseif ($topic === 'customer_self') {
            $fcmToken = $sender->getFcmToken();
            if (!$fcmToken) {
                return $this->json(['message' => 'No FCM token for user'], 200);
            }
            $this->firebase->sendToToken($fcmToken, $title, $msgBody, $data);

        } else {
            return $this->json(['message' => 'Unknown topic'], 400);
        }

        return $this->json(['message' => 'Notification sent']);
    }
}