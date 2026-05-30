<?php

namespace App\Controller;

use App\Enum\DeliveryStatus;
use App\Entity\Delivery;
use App\Repository\DeliveryRepository;
use App\Service\FirebaseDatabaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/delivery')]
class DeliveryTrackingController extends AbstractController
{
    private $database;

    public function __construct(
    FirebaseDatabaseService $firebaseDb,
    private MercurePublisher $mercure,  
) {
    $this->database = $firebaseDb->getDatabase();
}

    // ── Rider: get active delivery ────────────────────────────────────────────

    #[Route('/rider/active', name: 'rider_active_delivery', methods: ['GET'])]
    #[IsGranted('ROLE_STAFF')]
    public function getRiderActiveDelivery(DeliveryRepository $repo): JsonResponse
    {
        $user = $this->getUser();

        $delivery = $repo->findOneBy(['rider' => $user, 'status' => DeliveryStatus::IN_TRANSIT])
                 ?? $repo->findOneBy(['rider' => $user, 'status' => DeliveryStatus::PENDING]);

        if (!$delivery) {
            return $this->json(['deliveryId' => null, 'message' => 'No active delivery']);
        }

        return $this->json([
            'deliveryId'  => $delivery->getId(),
            'status'      => $delivery->getStatus()->value,
            'orderId'     => $delivery->getOrders()?->getId(),
            'orderNumber' => $delivery->getOrders()?->getOrderNumber(),
        ]);
    }

    // ── Customer + Rider: get delivery status ─────────────────────────────────
    // No ROLE_STAFF guard — customer needs this too

    #[Route('/{id}/status', name: 'delivery_status', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getStatus(int $id, DeliveryRepository $repo): JsonResponse
    {
        error_log('🔍 Fetching delivery status for ID: ' . $id);
        
        $delivery = $repo->find($id);

        if (!$delivery) {
            error_log('❌ Delivery not found for ID: ' . $id);
            return $this->json(['error' => 'Delivery not found', 'debugId' => $id], 404);
        }

        error_log('✅ Delivery found: ' . $delivery->getId());
        
        $order = $delivery->getOrders();
        $rider = $delivery->getRider();

        return $this->json([
            'deliveryId'  => $delivery->getId(),
            'status'      => $delivery->getStatus() instanceof \BackedEnum
                                ? $delivery->getStatus()->value
                                : $delivery->getStatus(),
            'orderId'     => $order?->getId(),
            'orderNumber' => $order?->getOrderNumber(),
            // Rider info for the customer tracking screen header
            'riderName'   => $rider
                                ? trim(($rider->getFirstname() ?? '') . ' ' . ($rider->getLastname() ?? ''))
                                  ?: $rider->getDisplayName() ?? 'Your Rider'
                                : 'Your Rider',
            'riderPhone'  => $rider?->getContactNumber(),
        ]);
    }

    // ── Rider: push GPS location to Firebase ──────────────────────────────────

    #[Route('/{id}/location', name: 'delivery_location_update', methods: ['POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function updateLocation(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $lat = $data['lat'] ?? null;
        $lng = $data['lng'] ?? null;

        if (!$lat || !$lng) {
            return $this->json(['error' => 'lat and lng are required'], 400);
        }

        $this->database
            ->getReference('deliveries/' . $id . '/location')
            ->set([
                'lat'        => $lat,
                'lng'        => $lng,
                'updated_at' => date('c'),
            ]);

        return $this->json(['success' => true]);
    }

    // ── Rider OR Customer: send chat message to Firebase ──────────────────────
    // Both roles can message — sender field distinguishes who sent it.
    // Removed ROLE_STAFF guard so customers can reply to the rider.

    #[Route('/{id}/message', name: 'delivery_send_message', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function sendMessage(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $text   = trim($data['text'] ?? '');
        $sender = $data['sender'] ?? 'customer'; // 'rider' | 'customer'

        if (!$text) {
            return $this->json(['error' => 'text is required'], 400);
        }

        // Prevent sender spoofing: force the sender value based on actual role
        $user  = $this->getUser();
        $roles = $user ? $user->getRoles() : [];
        $actualSender = in_array('ROLE_STAFF', $roles, true) || in_array('ROLE_ADMIN', $roles, true)
            ? 'rider'
            : 'customer';

        try {
            $firebasePath = 'deliveries/' . $id . '/messages';
            error_log('📤 Writing message to Firebase: ' . $firebasePath);
            
            $this->database
                ->getReference($firebasePath)
                ->push([
                    'sender'    => $actualSender,
                    'text'      => $text,
                    'timestamp' => round(microtime(true) * 1000), // JS-compatible ms timestamp
                ]);
            
            error_log('✅ Message written to Firebase successfully at: ' . $firebasePath);
            
            return $this->json([
                'success' => true,
                'firebasePath' => $firebasePath,
                'sender' => $actualSender,
                'timestamp' => round(microtime(true) * 1000),
            ]);
        } catch (\Exception $e) {
            error_log('❌ Firebase write error: ' . $e->getMessage());
            return $this->json(['error' => 'Failed to save message to Firebase', 'details' => $e->getMessage()], 500);
        }
    }

    // ── Rider: mark as delivered ──────────────────────────────────────────────

    #[Route('/{id}/update-status', name: 'delivery_update_status', methods: ['POST'])]
    #[IsGranted('ROLE_STAFF')]
public function updateStatus(
    int $id,
    Request $request,
    DeliveryRepository $repo,
    \Doctrine\ORM\EntityManagerInterface $em
): JsonResponse {
    $data   = json_decode($request->getContent(), true);
    $status = $data['status'] ?? null;

    $delivery = $repo->find($id);
    if (!$delivery) {
        return $this->json(['error' => 'Not found'], 404);
    }

    $delivery->setStatus(\App\Enum\DeliveryStatus::from($status));
    $delivery->setUpdatedAt(new \DateTime());
    $em->flush();

    // Push to Firebase (for the mobile app)
    $this->database
        ->getReference('deliveries/' . $id . '/status')
        ->set($status);

    // Push to Mercure (for the admin web table)  ← add this block
    $this->mercure->publishDeliveryUpdate([
        'totalDelivered' => $repo->countByStatus('delivered'),
        'totalPending'   => $repo->countByStatus('pending'),
        'delivery'       => [        // ← this is the key the JS watches for
            'id'     => $id,
            'status' => $status,
        ],
    ]);

    return $this->json(['success' => true]);
}

    
#[Route('/my-deliveries', name: 'my_deliveries', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
public function getMyDeliveries(DeliveryRepository $deliveryRepo): JsonResponse
{
    $user = $this->getUser();
    $roles = $user->getRoles() ?? [];
    $isRider = in_array('ROLE_STAFF', $roles) || in_array('ROLE_ADMIN', $roles);

    if ($isRider) {
        $deliveries = $deliveryRepo->findBy(['rider' => $user], ['id' => 'DESC']);
    } else {
        $deliveries = $deliveryRepo->createQueryBuilder('d')
            ->innerJoin('d.orders', 'o')
            ->where('o.createdBy = :user')
            ->andWhere('d.rider IS NOT NULL')  // ← just add this line
            ->setParameter('user', $user)
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    $data = array_map(function(Delivery $d) {
        return [
            'deliveryId' => $d->getId(),
            'orderNumber' => $d->getOrders()?->getOrderNumber() ?? 'N/A',
            'riderName' => $d->getRider()
                ? trim(($d->getRider()->getFirstname() ?? '') . ' ' . ($d->getRider()->getLastname() ?? ''))
                  ?: $d->getRider()->getDisplayName() ?? 'Your Rider'
                : null,
            'riderPhone' => $d->getRider()?->getContactNumber(),
            'status' => $d->getStatus() instanceof \BackedEnum
                ? $d->getStatus()->value
                : $d->getStatus(),
            'createdAt' => $d->getCreatedAt()?->format('c'),
        ];
    }, $deliveries);

    return $this->json($data);
}
}