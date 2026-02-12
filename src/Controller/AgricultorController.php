<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgricultorController extends AbstractController
{
    #[Route('/agricultor', name: 'app_agricultor_dashboard')]
    public function index(): Response
    {   
        $this->denyAccessUnlessGranted(attribute: 'IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        return $this->render('agricultor/index.html.twig', ['user' => $user]);
    }
}
