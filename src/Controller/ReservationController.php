<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\HoraireRepository;
use App\Repository\JourRepository;
use App\Repository\ReservationRepository;
use App\Repository\SeuilConviveRepository;
use DatePeriod;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReservationController extends AbstractController
{
    // Utilisés dans la fonction genererCreneaux() afin de traduire la chaine du jour d'un objet DateTime
    public function __construct(private TranslatorInterface $translator, private JourRepository $jourRepository)
    {}

    #[Route('/reservation', name: 'restaurant_reservation', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, HoraireRepository $horaireRepository, SeuilConviveRepository $conviveRepository, ReservationRepository $reservationRepository, MailerInterface $mailer): Response
    {
        // Récupère la chaine du jour pour la requête
        $chaineJour = (string) $request->request->get('dateJour');
        if (!empty($chaineJour) && $chaineJour !== "" && $request->isXmlHttpRequest()) {
            // Renvoi la liste des créneaux pour Ajax au format JSON
            return $this->json(['listeCreneaux' => $this->genererCreneaux($chaineJour)]);
        }

        $reservation = new Reservation();
        // La liste des créneaux seront affichés par Ajax au changement de la date de réservation
        // La date par défaut est vide pour forcer l'utilisateur a en sélectionner une
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $heureReservation = $request->request->get('reservation')['heure'];
            if (!is_null($heureReservation) && $heureReservation !== "") {
                // Si un utilisateur est connecté, son identifiant est ajouté à la réservation
                if (!is_null($this->getUser())) {
                    $reservation->setFkUtilisateur($this->getUser());
                    $reservation->setFkVisiteur(null);
                    $nombreConvivesReservation = $reservation->getFkUtilisateur()->getNombreConvives();
                }
                else {
                    // Si un utilisateur est connecté, son identifiant est ajouté à la réservation
                    $reservation->setFkUtilisateur(null);
                    $reservation->setFkVisiteur($reservation->getFkVisiteur());
                    $nombreConvivesReservation = $reservation->getFkVisiteur()->getNombreConvives();
                }

                // Définis l'heure de réservation dans l'entité
                $heureReservation = new DateTime($heureReservation);
                $form->getData()->setHeure($heureReservation);
                // Récupère le seuil de convive et le nombre de réservation pour cette date
                $seuil = $conviveRepository->findOneBy([], ['id' => 'ASC'])->getNombre();
                $nombreConvivesTotalReservation = 0;
                // Récupère le nombre de convives présent pour la date de cette réservation
                foreach ($reservationRepository->findBy(['date' => $reservation->getDate()]) as $uneReservation) {
                    if (!is_null($uneReservation->getFkUtilisateur())) {
                        $nombreConvivesTotalReservation += $uneReservation->getFkUtilisateur()->getNombreConvives();
                    }
                    else {
                        $nombreConvivesTotalReservation += $uneReservation->getFkVisiteur()->getNombreConvives();
                    }
                }
                // Calcul la différence entre le seuil maximal de convives et le nombre de convives réservés à cette date
                $nombreConvivesRestant = (int) $seuil - $nombreConvivesTotalReservation;

                // Si le créneau existe parmi la liste des créneaux pour cette date
                if (
                    $this->creneauExiste(
                        $this->genererCreneaux($reservation->getDate()->format('l')),
                        $reservation
                    )
                ) {
                    // S'il reste de la place pour de nouveaux convives
                    if ($nombreConvivesRestant > 0) {
                        // Si le nombre de convives encore acceptés pour cette date est supérieur au nombre de convives de la réservation
                        // et qu'un utilisateur ou visiteur est bien lié à la réservation
                        if (
                            $nombreConvivesRestant > $nombreConvivesReservation
                            && (!is_null($reservation->getFkUtilisateur()) || !is_null($reservation->getFkVisiteur()))
                        ) {
                            // Enregistrement de la réservation
                            $entityManager->persist($reservation);
                            $entityManager->flush();

                            // Définit le destinataire en fonction si c'est un utilisateur ou visiteur
                            if (!is_null($reservation->getFkUtilisateur())) {
                                $destinataire = $reservation->getFkUtilisateur()->getEmail();
                            }
                            else {
                                $destinataire = $reservation->getFkVisiteur()->getEmail();
                            }

                            // Récupère le lien du logo pour l'afficher dans le contenu du mail
                            $chemin = $this->getParameter('kernel.project_dir').'/public/images/logo.png';

                            // Envoi du mail de confirmation de réservation
                            $email = (new TemplatedEmail())
                                ->from(Address::create('Quai Antique <leblanc.romain.quai.antique@gmail.com>'))
                                ->to($destinataire)
                                // Passe le logo au template
                                ->embed(fopen($chemin, 'r'), 'logo')
                                ->subject('Enregistrement de votre réservation')
                                ->htmlTemplate('reservation/mail_enregistrement.html.twig')
                                ->context(['reservation' => $reservation])
                            ;

                            try {
                                // Envoi du mail puis affichage confirmation réservation
                                $mailer->send($email);
                                $this->addFlash('reservation', 'Votre réservation a été bien enregistrée. Un mail de confirmation vous a été envoyé également.');
                            } catch (\Exception $t) {
                                return new Response($t->getMessage());
                            }

                            return $this->redirectToRoute('restaurant_accueil');
                        }
                        else {
                            $message = "Le nombre de convives pour votre réservation dépasse le seuil de convives";
                        }
                    }
                    else {
                        $message = "Il n'est plus possible de réserver pour ce créneau (seuil de convives atteint)";
                    }
                }
                else {
                    $message = "Ce créneau n'existe pas pour cette date de réservation";
                }
            }
            else {
                $message = "Merci de sélectionner un créneau";
            }
            return $this->render('reservation/index.html.twig', [
                'errors' => $form->addError(new FormError($message))->getErrors(true),
                'formReservation' => $form->createView(),
            ]);
        }

        return $this->render('reservation/index.html.twig', [
            'errors' => $form->getErrors(true),
            'formReservation' => $form->createView(),
        ]);
    }

    /* Génère les créneaux de réservation à partir de la chaine du jour précisé en paramètre  */
    private function genererCreneaux(string $chaine) {
        // Récupération des heures d'ouverture/fermeture du jour
        $jour = $this->jourRepository->findOneBy(['libelle' => $this->translator->trans($chaine)]);
        // Définit le tableau multidimensionnel des créneaux du jour et de son index par défaut
        $tableauCreneaux = [];
        $cleJour = 0;
        $cleValeur = 0;
        $dateAujourdhui = new DateTime();
        $dateAujourdhui->setTimezone(new \DateTimeZone("Europe/Paris"));
        foreach ($jour->getHoraires()->getValues() as $uneHoraire) {
            // J'utilise DateTimeImmutable afin de copier les objets DateTime des horaires sans modifier leurs valeurs
            $date = new \DateTimeImmutable();
            $heureOuverture = $date->createFromMutable($uneHoraire->getHeureOuverture());
            $heureFermeture = $date->createFromMutable($uneHoraire->getHeureFermeture());
            // DatePeriod n'inclut pas le dernier créneau entre 2 dates donc je définis à -45 minutes pour l'inclure
            $heureFermeture = $heureFermeture->modify('-45 minutes');
            // Interval de temps de 15 minutes
            $creneau = new \DateInterval('PT15M');
            // Récupère les différents créneaux de réservation entre l'heure d'ouverture et fermeture
            // à l'aide de l'intervalle de temps
            $liste = new DatePeriod($heureOuverture, $creneau, $heureFermeture);
            // Boucle sur chaque créneau pour l'ajouter au tableau des créneaux au format heure/minutes
            foreach ($liste as $unCreneau) {
                // Si le jour de la date de réservation est le même que celui de la date du jour
                // et que l'heure du créneau n'est pas expiré (heure actuelle <= heure du créneau), on ajoute le créneau
                if ($chaine === $dateAujourdhui->format('l') && $dateAujourdhui->format('H:i') <= $unCreneau->format('H:i')) {
                    $tableauCreneaux[$cleJour][$cleValeur] = $unCreneau->format('H:i');
                }
                // Sinon si le jour de la date de réservation est bien différente de celle du jour, on ajoute le créneau
                // Les créneaux expirés à la date du jour ne sont pas ajoutés dans le tableau
                elseif($chaine !== $dateAujourdhui->format('l')) {
                    $tableauCreneaux[$cleJour][$cleValeur] = $unCreneau->format('H:i');
                }
                $cleValeur++;
            }
            $cleJour++;
        }
        return $tableauCreneaux;
    }

    #[Route('/reservation/nombre', name: 'restaurant_reservation_nombre', methods: ['GET', 'POST'])]
    public function reservation_nombre(SeuilConviveRepository $conviveRepository, ReservationRepository $reservationRepository, Request $request)
    {
        // Récupère la chaine du jour pour la requête
        $dateJour = (string) $request->request->get('dateTime');
        // Si la chaine n'est pas vide et que la requête est bien une requête asynchrone
        if(!is_null($dateJour) && $dateJour !== "" && $request->isXmlHttpRequest()) {
            // Transforme la chaine en objet DateTime
            $dateJour = new DateTime($dateJour);
            // Récupère le seuil de convive et le nombre de réservation pour cette date
            $seuil = $conviveRepository->findOneBy([], ['id' => 'ASC'])->getNombre();
            $nombreConvivesTotalReservation = 0;
            // Récupère le nombre de convives présent pour la date de cette réservation
            foreach ($reservationRepository->findBy(['date' => $dateJour]) as $uneReservation) {
                if (!is_null($uneReservation->getFkUtilisateur())) {
                    $nombreConvivesTotalReservation += $uneReservation->getFkUtilisateur()->getNombreConvives();
                }
                else {
                    $nombreConvivesTotalReservation += $uneReservation->getFkVisiteur()->getNombreConvives();
                }
            }
            // Calcul la différence entre le seuil maximal de convives et le nombre de convives réservés à cette date
            $nombreConvivesRestant = (int) $seuil - $nombreConvivesTotalReservation;
            return $this->json($nombreConvivesRestant);
        }
        else {
            return $this->json(false);
        }
    }

    /* Retourne VRAI si l'heure d'un créneau fait parti du tableau des différents créneaux possibles */
    private function creneauExiste(array $tableau, Reservation $reservation) {
        $existe = false;
        // Récupère la date/heure actuelle
        $dateAujourdhui = new DateTime();
        $dateAujourdhui->setTimezone(new \DateTimeZone("Europe/Paris"));
        // Définit le format de jour et récupère le créneau de réservation
        $formatJour = 'l';
        $heureReservation = $reservation->getHeure()->format('H:i');
        // Boucle sur le tableau multidimensionnel des créneaux possibles pour la date de réservation
        foreach ($tableau as $unCreneau) {
            // Si l'heure de la réservation choisie est bien dans le tableau des différents créneaux
            // et qu'elle n'a pas expirée
            if (in_array($heureReservation, $unCreneau)) {
                // Si le jour de la date de réservation est le même que celui d'aujourd'hui,
                // on vérifie que ce créneau n'est pas expiré
                if ($dateAujourdhui->format($formatJour) === $reservation->getDate()->format($formatJour)) {
                    if ($dateAujourdhui->format('H:i') <= $heureReservation) {
                        // Renvoi la valeur VRAI si elle n'est pas expiré pour ce jour
                        $existe = true;
                    }
                }
                else {
                    // Renvoi la valeur VRAI si elle existe bien et non expiré
                    $existe = true;
                }
            }
        }
        return $existe;
    }
}