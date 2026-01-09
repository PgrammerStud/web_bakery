<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\Security;
use App\Service\ActivityLoggerService;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_STAFF')")]
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
        $product = new Product();
        $product->setCreatedBy($this->getUser());
        $product->setCreatedAt(new \DateTimeImmutable());
        $product->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $activityLogger->log($this->getUser(), 'CREATE', "Product: {$product->getName()} (ID: {$product->getId()})");

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && !in_array('ROLE_STAFF', $user->getRoles(), true) && $product->getCreatedBy() !== $user) {
            $this->addFlash('error', 'You can only view your own products.');
            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && !in_array('ROLE_STAFF', $user->getRoles(), true) && $product->getCreatedBy() !== $user) {
            $this->addFlash('error', 'You can only edit your own products.');
            return $this->redirectToRoute('app_product_index');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $shouldLog = in_array('ROLE_ADMIN', $user->getRoles(), true) || in_array('ROLE_STAFF', $user->getRoles(), true) || $product->getCreatedBy() === $user;
            if ($shouldLog) {
                $activityLogger->log($user, 'UPDATE', "Product: {$product->getName()} (ID: {$product->getId()})");
            }

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && !in_array('ROLE_STAFF', $user->getRoles(), true) && $product->getCreatedBy() !== $user) {
            $this->addFlash('error', 'You can only delete your own products.');
            return $this->redirectToRoute('app_product_index');
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $shouldLog = in_array('ROLE_ADMIN', $user->getRoles(), true) || in_array('ROLE_STAFF', $user->getRoles(), true) || $product->getCreatedBy() === $user;
            if ($shouldLog) {
                $activityLogger->log($user, 'DELETE', "Product: {$product->getName()} (ID: {$product->getId()})");
            }
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
