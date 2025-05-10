<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'app_users')]
    public function index(UserRepository $rep): Response
    {
        $users = $rep->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/all', name: 'app_users_all')]
    public function showAll(UserRepository $rep, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $rep->findAll();
        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('user/all.html.twig', ['users' => $users]);
    }

    #[Route('/admin/users/create', name: 'app_users_create')]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode le mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);

            // Définir les rôles en fonction du choix de l'utilisateur
            $userRole = $form->get('userRole')->getData();
            if ($userRole === 'admin') {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur ajouté avec succès');
            return $this->redirectToRoute('app_users_all');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/users/update/{id}', name: 'app_users_update')]
    public function update(Request $request, User $user, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $originalPassword = $user->getPassword();

        $form = $this->createForm(UserType::class, $user, [
            'require_password' => false,
        ]);

        // Prérégler le champ userRole en fonction du rôle actuel de l'utilisateur
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $form->get('userRole')->setData('admin');
        } else {
            $form->get('userRole')->setData('client');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si un nouveau mot de passe est fourni, le hasher
            if (!empty($user->getPassword())) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                );
                $user->setPassword($hashedPassword);
            } else {
                // Sinon, conserver l'ancien mot de passe
                $user->setPassword($originalPassword);
            }

            // Mettre à jour les rôles en fonction du choix de l'utilisateur
            $userRole = $form->get('userRole')->getData();
            if ($userRole === 'admin') {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            $em->flush();
            $this->addFlash('success', 'Utilisateur mis à jour avec succès');
            return $this->redirectToRoute('app_users_all');
        }

        return $this->render('user/update.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/admin/users/show/{id}', name: 'app_users_show')]
    public function show(User $user): Response
    {
        return $this->render('user/detail.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/admin/users/delete/{id}', name: 'app_users_delete')]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Utilisateur supprimé avec succès');
        return $this->redirectToRoute('app_users_all');
    }

    #[Route('/admin/users/admins', name: 'app_users_admins')]
    public function listAdmins(UserRepository $repository): Response
    {
        $admins = $repository->findAdmins();
        return $this->render('user/admins.html.twig', [
            'users' => $admins
        ]);
    }

    #[Route('/admin/users/clients', name: 'app_users_clients')]
    public function listClients(UserRepository $repository): Response
    {
        $clients = $repository->findClients();
        return $this->render('user/clients.html.twig', [
            'users' => $clients
        ]);
    }
}
