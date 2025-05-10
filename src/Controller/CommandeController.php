<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

final class CommandeController extends AbstractController
{
    #[Route('/admin/Commandes/all', name: 'app_commandes_all')]
    public function show4(CommandeRepository $rep, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $rep->findAll();
        $Commandes = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), /* page number */
            5
        );
        return $this->render('Commande/all.html.twig', ['Commandes' => $Commandes]);
    }

    #[Route('/admin/Commandes/show/{id}', name: 'app_commandes_show')]
    public function show(CommandeRepository $rep, int $id): Response
    {
        $commande = $rep->find($id);
        if (!$commande) {
            throw $this->createNotFoundException("Commande non trouvée");
        }

        return $this->render('Commande/detail.html.twig', ['commande' => $commande]);
    }
}
