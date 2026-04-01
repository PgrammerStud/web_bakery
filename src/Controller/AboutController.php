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
                'position' => 'Master Baker',
                'bio' => 'Expert in artisan bread and pastries, Jennie brings creativity and precision to every loaf. Her passion for baking is evident in every product.',
                'image' => 'desiree.jpg'
            ],
            [
                'name' => 'Lisa Manoban',
                'position' => 'Customer Relations Manager',
                'bio' => 'Lisa ensures every customer experience is exceptional. She coordinates special orders and manages our community relationships with dedication.',
                'image' => 'desiree.jpg'
            ],
            [
                'name' => 'Rosé Park',
                'position' => 'Production Manager',
                'bio' => 'Rosé oversees our production process, ensuring quality control and timely delivery of all our baked products to customers.',
                'image' => 'desiree.jpg'
            ]
        ];

        return $this->render('about/index.html.twig', [
            'teamMembers' => $teamMembers,
        ]);
    }
}
