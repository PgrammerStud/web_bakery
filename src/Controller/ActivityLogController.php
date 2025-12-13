<?php

namespace App\Controller;

use App\Repository\ActivityLogRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ActivityLogController extends AbstractController
{
    #[Route('/activity-log', name: 'app_activity_log')]
    public function index(
        Request $request,
        ActivityLogRepository $activityLogRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $qb = $entityManager->createQueryBuilder()
            ->select('al')
            ->from('App\Entity\ActivityLog', 'al')
            ->orderBy('al.createdAt', 'DESC');

        // Filter by user
        if ($userId = $request->query->get('user')) {
            $qb->andWhere('al.user = :user')
               ->setParameter('user', $userId);
        }

        // Filter by action
        if ($action = $request->query->get('action')) {
            $qb->andWhere('al.action = :action')
               ->setParameter('action', $action);
        }

        // Filter by date range
        if ($dateFrom = $request->query->get('date_from')) {
            $qb->andWhere('al.createdAt >= :dateFrom')
               ->setParameter('dateFrom', new \DateTime($dateFrom));
        }
        if ($dateTo = $request->query->get('date_to')) {
            $qb->andWhere('al.createdAt <= :dateTo')
               ->setParameter('dateTo', new \DateTime($dateTo . ' 23:59:59'));
        }

        $logs = $qb->getQuery()->getResult();

        // Get all users for filter dropdown
        $users = $userRepository->findAll();

        // Available actions
        $actions = ['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT'];

        return $this->render('activity_log/index.html.twig', [
            'activity_logs' => $logs,
            'users' => $users,
            'actions' => $actions,
            'filters' => $request->query->all(),
        ]);
    }
}