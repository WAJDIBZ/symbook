<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Livres;
use App\Repository\CategorieRepository;
use App\Repository\LivresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(LivresRepository $livresRepository, CategorieRepository $categorieRepository): Response
    {
        // (8 maximum)
        $latestBooks = $livresRepository->findBy([], ['dateEdition' => 'DESC'], 8);

        // Récupérer toutes les catégories
        $categories = $categorieRepository->findAll();

        // Récupérer quelques livres en promotion ou vedette (remplacer par une logique métier appropriée)
        $featuredBooks = $livresRepository->findBy([], ['prix' => 'ASC'], 4);

        return $this->render('home.html.twig', [
            'latestBooks' => $latestBooks,
            'categories' => $categories,
            'featuredBooks' => $featuredBooks,
        ]);
    }
}
