<?php

declare(strict_types=1);

/*
 * This file is part of the eRecht24 custom sections extension for Contao.
 *
 * (c) DIGIWERFT GmbH
 *
 * @license MIT
 */

namespace Digiwerft\ContaoEr24CustomSections\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Digiwerft\ContaoEr24CustomSections\ContaoEr24CustomSectionsBundle;
use Fenepedia\ContaoErecht24Rechtstexte\ContaoErecht24RechtstexteBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoEr24CustomSectionsBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, ContaoErecht24RechtstexteBundle::class]),
        ];
    }
}
