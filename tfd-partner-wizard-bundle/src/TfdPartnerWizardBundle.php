<?php

namespace Tfd\PartnerWizardBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class TfdPartnerWizardBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
