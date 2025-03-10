<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home.html.twig', [
            'name' => 'Wajdi',
            'projects' => [
                [
                    'title' => 'Project 1',
                    'description' => 'A amazing Symfony project',
                    'image' => 'project1.jpg'
                ],
                [
                    'title' => 'Project 2',
                    'description' => 'E-commerce platform',
                    'image' => 'project2.jpg'
                ],
                [
                    'title' => 'Project 3',
                    'description' => 'Mobile application',
                    'image' => 'project3.jpg'
                ]
            ]
        ]);
    }
}
