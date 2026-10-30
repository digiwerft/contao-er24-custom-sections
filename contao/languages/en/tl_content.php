<?php

declare(strict_types=1);

/*
 * This file is part of the eRecht24 custom sections extension for Contao.
 *
 * (c) DIGIWERFT GmbH
 *
 * @license MIT
 */

use Digiwerft\ContaoEr24CustomSections\LegalText\LegalTextDocument;

$GLOBALS['TL_LANG']['tl_content']['er24cs_legend'] = 'Position in the legal text';
$GLOBALS['TL_LANG']['tl_content']['er24CsTarget'] = ['eRecht24 legal text', 'The eRecht24 element in this article the section is inserted into.'];
$GLOBALS['TL_LANG']['tl_content']['er24CsPosition'] = ['Position', '"After the section" inserts behind the section including its sub sections.'];
$GLOBALS['TL_LANG']['tl_content']['er24CsAnchor'] = ['Section', 'The list is built from the most recently loaded version of the legal text. Chapter numbers are ignored, so renumbering by eRecht24 does not matter.'];
$GLOBALS['TL_LANG']['tl_content']['er24CsHeadline'] = ['Headline', 'The headline level is adjusted to the selected section automatically.'];

$GLOBALS['TL_LANG']['tl_content']['er24CsPositions'] = [
    LegalTextDocument::POSITION_AFTER => 'After the section',
    LegalTextDocument::POSITION_BEFORE => 'Before the section',
];

$GLOBALS['TL_LANG']['tl_content']['er24CsAnchorGone'] = '⚠ No longer part of the legal text: %s';
$GLOBALS['TL_LANG']['tl_content']['er24CsAnchorNotFound'] = 'Custom section "%s" (ID %s): The section "%s" no longer exists in the eRecht24 text, so the custom section is output at the end of the legal text. Please select a new section.';
$GLOBALS['TL_LANG']['tl_content']['er24CsAnchorMoved'] = 'eRecht24 moved the section or renamed its chapter. It was found by its name. Please check the selection and save the element once.';
$GLOBALS['TL_LANG']['tl_content']['er24CsInsertedAt'] = '%s "%s" (legal text ID %s)';
$GLOBALS['TL_LANG']['tl_content']['er24CsTargetMissing'] = 'No eRecht24 legal text selected.';
$GLOBALS['TL_LANG']['tl_content']['er24CsTargetNotLoaded'] = 'The legal text (ID %s) has not been loaded from eRecht24 yet. The position can be checked once it has been displayed.';
