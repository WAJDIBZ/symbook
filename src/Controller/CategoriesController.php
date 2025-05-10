<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\CategoriesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class CategoriesController extends AbstractController
{
    #[Route('/admin/categories', name: 'app_categories')]
    public function index(CategorieRepository $rep): Response
    {
        $categories = $rep->findAll();
        return $this->render('categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }
    #[Route('/admin/categories/create', name: 'app_categories_create')]
    public function create(Request $request,EntityManagerInterface $em): Response
    {
        $categorie = new Categorie();
        //afficher le formulaire
       $form = $this->createForm(CategoriesType::class, $categorie)->handleRequest($request);
       if ($form->isSubmitted() && $form->isValid()) {
           //traitement du formulaire
           $categorie = $form->getData();
           $em->persist($categorie);
           $em->flush();
           $this->addFlash('success', 'Catégorie ajoutée avec succès');
           return $this->redirectToRoute('app_categories');
       }
       //traitement du formulaire
       return $this->render('categories/create.html.twig', [
            'f' => $form ,
        ]);
        
    }
    #[Route('/admin/categories/update/{id}', name: 'app_categories_update')]
    public function update(Request $request, CategorieRepository $rep, EntityManagerInterface $em, int $id): Response
    {
        $categorie = $rep->find($id);
        if (!$categorie) {
            throw $this->createNotFoundException("Catégorie non trouvée");
        }
        //afficher le formulaire
       $form = $this->createForm(CategoriesType::class, $categorie)->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid()) {
              //traitement du formulaire
             
              $em->flush();
              $this->addFlash('success', 'Catégorie modifiée avec succès');
              return $this->redirectToRoute('app_categories');
         }
        //traitement du formulaire
        return $this->render('categories/update.html.twig', [
            'f' => $form ,
        ]);}
    #[Route('/admin/livres/delete/{id}', name: 'app_categorie_delete')]
    public function delete(Categorie $categorie, EntityManagerInterface $em): Response
    {
        $em->remove($categorie);
        $em->flush();
        $this->addFlash('success', 'Catégorie supprimée avec succès');
        return $this->redirectToRoute('app_categories');
    }

    }
