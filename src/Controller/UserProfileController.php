<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('ROLE_USER')]
class UserProfileController extends AbstractController
{
    #[Route('/profile', name: 'user_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');
            $phone = $request->request->get('phone');
            $newPassword = $request->request->get('newPassword');
            $confirmPassword = $request->request->get('confirmPassword');

            // Update basic info
            if ($firstName) {
                $user->setFirstName($firstName);
            }
            if ($lastName) {
                $user->setLastName($lastName);
            }
            if ($phone) {
                $user->setPhone($phone);
            }

            // Update password if provided
            if ($newPassword) {
                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                    return $this->redirectToRoute('user_profile');
                }
                if (strlen($newPassword) < 6) {
                    $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
                    return $this->redirectToRoute('user_profile');
                }
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès!');
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/my-orders', name: 'user_orders', methods: ['GET'])]
    public function myOrders(CommandeRepository $commandeRepository): Response
    {
        $orders = $commandeRepository->findBy(
            ['user' => $this->getUser()],
            ['dateCommande' => 'DESC']
        );

        return $this->render('user/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/my-orders/{id}', name: 'user_order_details', methods: ['GET'])]
    public function orderDetails(Commande $commande): Response
    {
        // Ensure user can only see their own orders
        if ($commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('user/order_details.html.twig', [
            'order' => $commande,
        ]);
    }

    #[Route('/my-orders/{id}/cancel', name: 'user_order_cancel', methods: ['POST'])]
    public function cancelOrder(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        // Ensure user can only cancel their own orders
        if ($commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Can only cancel pending or confirmed orders
        if (!in_array($commande->getStatut(), ['Confirmée', 'En attente'])) {
            $this->addFlash('error', 'Cette commande ne peut pas être annulée.');
            return $this->redirectToRoute('user_order_details', ['id' => $commande->getId()]);
        }

        // Verify CSRF token
        if (!$this->isCsrfTokenValid('cancel' . $commande->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('user_order_details', ['id' => $commande->getId()]);
        }

        $commande->setStatut('Annulée');
        $entityManager->flush();

        $this->addFlash('success', 'Commande annulée avec succès!');
        return $this->redirectToRoute('user_orders');
    }
}
