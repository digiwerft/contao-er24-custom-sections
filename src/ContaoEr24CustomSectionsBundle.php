<?php

declare(strict_types=1);

/*
 * This file is part of the eRecht24 custom sections extension for Contao.
 *
 * (c) DIGIWERFT GmbH
 *
 * @license MIT
 */

namespace Digiwerft\ContaoEr24CustomSections;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoEr24CustomSectionsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
