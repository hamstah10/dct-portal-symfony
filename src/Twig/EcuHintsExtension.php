<?php

namespace App\Twig;

use App\Entity\Ecu;
use App\Service\EcuHintsResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EcuHintsExtension extends AbstractExtension
{
    public function __construct(private readonly EcuHintsResolver $resolver) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ecu_hints_for_ecu', $this->resolver->resolveForEcu(...)),
        ];
    }
}
