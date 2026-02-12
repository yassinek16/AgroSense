<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TechnicienController extends AbstractController
{
    #[Route('/technicien', name: 'app_technicien_dashboard')]
    public function index(): Response
    {   
        $this->denyAccessUnlessGranted(attribute: 'IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        return $this->render('technicien/index.html.twig', ['user' => $user]);
    }
}
