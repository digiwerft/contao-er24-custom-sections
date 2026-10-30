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

$GLOBALS['TL_LANG']['tl_content']['er24cs_legend'] = 'Position im Rechtstext';
$GLOBALS['TL_LANG']['tl_content']['er24CsTarget'] = ['eRecht24-Rechtstext', 'Das eRecht24-Element in diesem Artikel, in das der Abschnitt eingefügt wird.'];
$GLOBALS['TL_LANG']['tl_content']['er24CsPosition'] = ['Position', '„Nach dem Abschnitt“ fügt hinter dem Abschnitt inklusive seiner Unterabschnitte ein.'];
$GLOBALS['TL_LANG']['tl_content']['er24CsAnchor'] = ['Abschnitt', 'Die Liste stammt aus der zuletzt geladenen Version des Rechtstexts. Kapitelnummern werden beim Abgleich ignoriert, eine Umnummerierung durch eRecht24 ist also unkritisch.'];
$GLOBALS['TL_LANG']['tl_content']['er24CsHeadline'] = ['Überschrift', 'Die Überschriftenebene wird automatisch an den gewählten Abschnitt angepasst.'];

$GLOBALS['TL_LANG']['tl_content']['er24CsPositions'] = [
    LegalTextDocument::POSITION_AFTER => 'Nach dem Abschnitt',
    LegalTextDocument::POSITION_BEFORE => 'Vor dem Abschnitt',
];

$GLOBALS['TL_LANG']['tl_content']['er24CsAnchorGone'] = '⚠ Nicht mehr im Rechtstext vorhanden: %s';
$GLOBALS['TL_LANG']['tl_content']['er24CsAnchorNotFound'] = 'Rechtstext-Ergänzung „%s“ (ID %s): Der Abschnitt „%s“ existiert im eRecht24-Text nicht mehr. Die Ergänzung wird deshalb am Ende des Rechtstexts ausgegeben. Bitte einen neuen Abschnitt wählen.';
$GLOBALS['TL_LANG']['tl_content']['er24CsAnchorMoved'] = 'Der Abschnitt wurde von eRecht24 verschoben oder sein Kapitel umbenannt. Er wurde anhand seines Namens gefunden. Bitte die Auswahl prüfen und das Element einmal speichern.';
$GLOBALS['TL_LANG']['tl_content']['er24CsInsertedAt'] = '%s „%s“ (Rechtstext ID %s)';
$GLOBALS['TL_LANG']['tl_content']['er24CsTargetMissing'] = 'Kein eRecht24-Rechtstext gewählt.';
$GLOBALS['TL_LANG']['tl_content']['er24CsTargetNotLoaded'] = 'Der Rechtstext (ID %s) wurde noch nicht von eRecht24 geladen. Die Position kann erst geprüft werden, wenn er einmal angezeigt wurde.';
