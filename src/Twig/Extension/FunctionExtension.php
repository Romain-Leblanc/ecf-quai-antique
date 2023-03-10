<?php

namespace App\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FunctionExtension extends AbstractExtension
{
    public function __construct(private KernelInterface $kernel)
    {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('heureMinute', [$this, 'heureMinute']),
            new TwigFunction('montantEuros', [$this, 'montantEuros']),
            new TwigFunction('numeroTelephone', [$this, 'numeroTelephone']),
            new TwigFunction('fichierExiste', [$this, 'fichierExiste']),
            new TwigFunction('menuActif', [$this, 'menuActif']),
        ];
    }

    // Retourne le n° de téléphone avec un point entre chaque paire de chiffres
    function numeroTelephone(string $numero){
        return wordwrap($numero, 2, '.', true);
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
