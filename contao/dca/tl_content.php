<?php

declare(strict_types=1);

/*
 * This file is part of the eRecht24 custom sections extension for Contao.
 *
 * (c) DIGIWERFT GmbH
 *
 * @license MIT
 */

use Digiwerft\ContaoEr24CustomSections\Controller\ContentElement\CustomSectionElementController;
use Digiwerft\ContaoEr24CustomSections\LegalText\LegalTextDocument;

$GLOBALS['TL_DCA']['tl_content']['palettes'][CustomSectionElementController::TYPE] =
    '{type_legend},type;{er24cs_legend},er24CsTarget,er24CsPosition,er24CsAnchor;{text_legend},er24CsHeadline,text;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop'
;

$GLOBALS['TL_DCA']['tl_content']['fields']['er24CsTarget'] = [
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
    'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['er24CsPosition'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => [LegalTextDocument::POSITION_AFTER, LegalTextDocument::POSITION_BEFORE],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['er24CsPositions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 8, 'default' => LegalTextDocument::POSITION_AFTER],
];

// The key contains the slugs of the heading and all its parents, so it can get long
$GLOBALS['TL_DCA']['tl_content']['fields']['er24CsAnchor'] = [
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr long'],
    'sql' => 'text NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['er24CsHeadline'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr long'],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];
