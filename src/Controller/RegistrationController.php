<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $unUtilisateur = new Utilisateur();
        $formInscription = $this->createForm(RegistrationFormType::class, $unUtilisateur);
        $formInscription->handleRequest($request);

        if ($formInscription->isSubmitted() && $formInscription->isValid()) {
            // encode the plain password
            $unUtilisateur->setPassword(
                $userPasswordHasher->hashPassword(
                    $unUtilisateur,
                    $formInscription->get('plainPassword')->getData()
                )
            );
            // Nom en majucule et le prénom n'aura que la 1ère lettre en majuscule
            $unUtilisateur->setNom(mb_strtoupper($unUtilisateur->getNom()));
            $unUtilisateur->setPrenom(ucfirst($unUtilisateur->getPrenom()));

            $entityManager->persist($unUtilisateur);
            $entityManager->flush();

            // Récupère le lien du logo pour l'afficher dans le contenu du mail
            $chemin = $this->getParameter('kernel.project_dir')."/public/images/logo.png";

            $email = (new TemplatedEmail())
                ->from(Address::create('Quai Antique <leblanc.romain.quai.antique@gmail.com>'))
                ->to($unUtilisateur->getEmail())
                // Définit le logo en tant que
                ->embed(fopen($chemin, 'r'), 'logo')
                ->subject('Bienvenue au restaurant Quai Antique !')
                ->htmlTemplate('registration/mail_inscription.html.twig')
                ->context(['unUtilisateur' => $unUtilisateur])
            ;

            try {
                $mailer->send($email);
                $this->addFlash('inscription', 'Votre inscription a été bien enregistrée. Veuillez vous connecter.');
            } catch (\Exception $t) {
                return new Response($t->getMessage());
            }

            return $this->redirectToRoute('app_connexion');
        }

        return $this->render('registration/register.html.twig', [
            'formInscription' => $formInscription->createView(),
            'error' => $formInscription->getErrors(true)
        ]);
    }
}
