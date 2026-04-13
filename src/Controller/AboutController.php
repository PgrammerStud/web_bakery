<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    #[Route('/about', name: 'app_about')]
    public function index(): Response
    {
        $teamMembers = [
            [
                'name' => 'Desiree Catalbas',
                'position' => 'Head Baker & Founder',
                'bio' => 'With over 20 years of baking experience, Desiree founded Catalbas Bakery with a passion for traditional baking techniques and fresh ingredients.',
                'image' => 'desiree.jpg'
            ],
            [
                'name' => 'Jennie Kim',
                'position' => 'Assistant Baker',
                'bio' => 'Helps the head baker preparing, baking and packaging products.',
                'image' => 'desiree.jpg'
            ],
            [
                'name' => 'Lisa Manoban',
                'position' => 'Inventory & Supply Manager',
                'bio' => 'Manages inventory and supply chain operations to ensure seamless production and delivery and coordinates with shelters and beneficiaries.',
                'image' => 'desiree.jpg'
            ],
            [
                'name' => 'Rosé Park',
                'position' => 'Delivery Staff & Rider',
                'bio' => 'Responsible for delivering our fresh baked goods to customers and ensuring timely and safe delivery.',
                'image' => 'desiree.jpg'
            ],

            [
                'name' => 'Jisoo Kim',
                'position' => 'System/Marketing Manager',
                'bio' => 'Manages the website and marketing efforts to promote Catalbas Bakery.',
                'image' => 'desiree.jpg'
            ],

            [
                'name' => 'Desiree Catalbas',
                'position' => 'System/Marketing Manager',
                'bio' => 'Manages the website and marketing efforts to promote Catalbas Bakery.',
                'image' => 'desiree.jpg'
            ]
        ];

        return $this->render('about/index.html.twig', [
            'teamMembers' => $teamMembers,
        ]);
    }
}
