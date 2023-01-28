<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\FunctionExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FunctionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('nomPrenom', [$this, 'nomPrenom']),
            new TwigFunction('heureMinute', [$this, 'heureMinute']),
            new TwigFunction('montantEuros', [$this, 'montantEuros']),
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
}
