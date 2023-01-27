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
        ];
    }

    // Retourne le nom et prénom
    function nomPrenom(string $nom, string $prenom){
        return mb_strtoupper($nom)." ".ucfirst($prenom);
    }
}
