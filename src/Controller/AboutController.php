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
                'name' => 'Maria Santos',
                'position' => 'Head Baker & Founder',
                'bio' => 'With over 20 years of baking experience, Maria founded Catalbas Bakery with a passion for traditional baking techniques and fresh ingredients.',
                'image' => 'team-1.jpg'
            ],
            [
                'name' => 'Juan Cruz',
                'position' => 'Master Baker',
                'bio' => 'Expert in artisan bread and pastries, Juan brings creativity and precision to every loaf. His passion for baking is evident in every product.',
                'image' => 'team-2.jpg'
            ],
            [
                'name' => 'Rosa Mendoza',
                'position' => 'Customer Relations Manager',
                'bio' => 'Rosa ensures every customer experience is exceptional. She coordinates special orders and manages our community relationships with dedication.',
                'image' => 'team-3.jpg'
            ],
            [
                'name' => 'Carlos Reyes',
                'position' => 'Production Manager',
                'bio' => 'Carlos oversees our production process, ensuring quality control and timely delivery of all our baked products to customers.',
                'image' => 'team-4.jpg'
            ]
        ];

        return $this->render('about/index.html.twig', [
            'teamMembers' => $teamMembers,
        ]);
    }
}
