<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\FunctionExtensionRuntime;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FunctionExtension extends AbstractExtension
{
    public function __construct(private KernelInterface $kernel)
    {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('nomPrenom', [$this, 'nomPrenom']),
            new TwigFunction('heureMinute', [$this, 'heureMinute']),
            new TwigFunction('montantEuros', [$this, 'montantEuros']),
            new TwigFunction('fichierExiste', [$this, 'fichierExiste']),
            new TwigFunction('menuActif', [$this, 'menuActif']),
        ];
    }

    // Retourne le nom et prénom
    function nomPrenom(string $nom, string $prenom){
        return mb_strtoupper($nom)." ".ucfirst($prenom);
    }

    // Retourne l'heure et minutes
    function heureMinute(\DateTime $heureOuverture, \DateTime $heureFermeture){
        $format = 'H:i';
        return $heureOuverture->format($format)." - ".$heureFermeture->format($format);
    }

    // Retourne le montant en euros
    function montantEuros(float $montant){
        return number_format($montant, 2, ',', ' ')." €";
    }

    // Retourne VRAI si le lien du fichier en paramètre existe
    function fichierExiste(string $path)
    {
        return is_file(realpath($this->kernel->getProjectDir() . '/public/'.$path));
    }

    // Met en surbrillance le lien du menu qui correspond au slug de la route actuelle
    function menuActif(string $page, string $menu) {
        // Scinde la chaine en tableau avec un "_" pour récupérer le nom précis de la route
        // (par exemple "accueil" pour "restaurant_accueil")
        $explode = explode("_", $page)[1];
        // Si la valeur à l'index un fait partie du tableau, on met en surbrillance
        if($explode === $menu) {
            return "active";
        }
        else {
            return "";
        }
    }
}
