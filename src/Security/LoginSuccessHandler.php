<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $user = $token->getUser();
        
        // Redirection basée sur les rôles
        foreach ($user->getRoles() as $role) {
            if ($role === 'ROLE_ADMIN') {
                return new RedirectResponse($this->router->generate('app_admin'));
            }
            if ($role === 'ROLE_AGRICULTOR') {
                return new RedirectResponse($this->router->generate('app_agricultor_dashboard'));
            }
            if ($role === 'ROLE_TECHNICIEN') {
                return new RedirectResponse($this->router->generate('app_technicien_dashboard'));
            }
        }

        // Redirection par défaut
        return new RedirectResponse($this->router->generate('app_home'));
    }
}
