<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class MoortechGrasbergContaoSocializedBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
