<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_connexion')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si un utilisateur est déjà connecté,on le déconnecte
        // afin d'éviter une usurpation du compte utilisateur
         if ($this->getUser()) {
             return $this->redirectToRoute('app_deconnexion');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $unUtilisateur = new Utilisateur();
        $formConnexion = $this->createForm(LoginFormType::class, $unUtilisateur);

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'formConnexion' => $formConnexion->createView(),
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_deconnexion')]
    public function logout(): void
    {
//        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
