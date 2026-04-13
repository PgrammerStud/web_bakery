<?php

namespace App\Controller;

use App\Entity\Bakeitforward;
use App\Entity\Bakeitforwardwallet;
use App\Repository\BakeitforwardRepository;
use App\Repository\BakeitforwardwalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/bakeitforward')]
final class BakeItForwardController extends AbstractController
{
    #[Route('/feature', name: 'app_bakeitforward_feature', methods: ['GET'])]
    public function feature(): Response
    {
        return $this->render('bakeitforward/feature.html.twig');
    }

    #[Route('/', name: 'app_bakeitforward_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(BakeitforwardRepository $bakeitforwardRepository, BakeitforwardwalletRepository $walletRepository): Response
    {
        $contributions = $bakeitforwardRepository->findBy([], ['created_at' => 'DESC'], 50);
        $wallet = $walletRepository->findOneBy([]) ?? new Bakeitforwardwallet();

        return $this->render('bakeitforward/index.html.twig', [
            'contributions' => $contributions,
            'wallet' => $wallet,
        ]);
    }

    #[Route('/{id}/mark-donated', name: 'app_bakeitforward_mark_donated', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function markDonated(Bakeitforward $bakeitforward, EntityManagerInterface $entityManager): Response
    {
        $bakeitforward->setDonated(true);
        $entityManager->flush();

        $this->addFlash('success', 'Contribution marked as donated.');

        return $this->redirectToRoute('app_bakeitforward_index');
    }
}