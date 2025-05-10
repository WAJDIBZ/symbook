<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Livres;
use App\Repository\LivresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\LivresType;
use App\Repository\CategorieRepository;

final class LivresController extends AbstractController
{
    #[Route('/livres/create', name: 'app_livres_create')]
    public function create(EntityManagerInterface $em): Response
    {
        $livre = new Livres();
        $date = new \DateTime();
        $dateString = $date->format('Y-m-d H:i:s');
        $livre->setTitre('titre 1')
            ->setIsbn('isbn 1')
            ->setEditeur('editeur 1')
            ->setDateEdition($date)
            ->setPrix(10.99)
            ->setImage('ttps://picsum.photos/200/300')
            ->setSlug('slug-1');
        $em->persist($livre);
        $em->flush();
        dd($livre);
        return new Response('Livre créé');
    }
    #[Route('/admin/livres/show/{id}', name: 'app_livres_show')]
    public function show1(LivresRepository $rep, int $id): Response
    {
        $livre = $rep->find($id);
        if (!$livre) {
            throw $this->createNotFoundException("Livre $id  non trouvé");
        }

        return $this->render('livres/show.html.twig', ['livre' => $livre]);
    }
    #[Route('/livres/show2', name: 'app_livres_show2')]
    public function show2(LivresRepository $rep): Response
    {
        $livre = $rep->findOneBy(['titre' => 'titre 1']);
        if (!$livre) {
            throw $this->createNotFoundException("Livre non trouvé");
        }
        dd($livre);
    }
    #[Route('/admin/livres/show3', name: 'app_livres_show3')]
    public function show3(LivresRepository $rep): Response
    {
        $livres = $rep->findBy(['titre' => 'titre 1', 'editeur' => 'editeur 1'], ['prix' => 'ASC']);
        dd($livres);
    }
    #[Route('/admin/livres/all', name: 'app_livres_all')]
    public function show4(LivresRepository $rep, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $rep->findAll();
        $livres = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), /* page number */
            5
        );
        return $this->render('livres/all.html.twig', ['livres' => $livres]);
    }
    #[Route('/admin/livres/delete/{id}', name: 'app_livres_delete')]
    public function delete(Livres $livre, EntityManagerInterface $em): Response
    {
        $em->remove($livre);
        $em->flush();
        dd($livre);
    }
    #[Route('/admin/livres/update/{id}', name: 'app_livres_update')]
    public function update(Request $request, Livres $livre, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LivresType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Livre mis à jour avec succès');

            return $this->redirectToRoute('app_livres_all');
        }

        return $this->render('livres/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('admin/livres/new', name: 'app_livres_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $livre = new Livres();
        $form = $this->createForm(LivresType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($livre);
            $em->flush();
            $this->addFlash('success', 'Livre ajouté avec succès');

            return $this->redirectToRoute('app_livres_all', ['id' => $livre->getId()]);
        }

        return $this->render('livres/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/categorie/{slug}', name: 'app_livres_by_category')]
    public function livresByCategory(string $slug, CategorieRepository $categorieRepository, LivresRepository $livresRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $categorie = $categorieRepository->findOneBy(['slug' => $slug]);

        if (!$categorie) {
            throw $this->createNotFoundException('Cette catégorie n\'existe pas');
        }

        $livres = $paginator->paginate(
            $livresRepository->findBy(['Categorie' => $categorie]),
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('livres/by_category.html.twig', [
            'categorie' => $categorie,
            'livres' => $livres,
        ]);
    }

    #[Route('/livres/by-category/{id}', name: 'app_livres_by_category_id')]
    public function livresByCategoryId(
        int $id,
        CategorieRepository $categorieRepository,
        LivresRepository $livresRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $category = $categorieRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException("Catégorie non trouvée");
        }

        $query = $livresRepository->findBy(['categorie' => $category]);

        $livres = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6 // Number of books per page
        );

        return $this->render('livres/by_category.html.twig', [
            'category' => $category,
            'livres' => $livres
        ]);
    }
}
