<?php

declare(strict_types=1);

/*
 * This file is part of the eRecht24 custom sections extension for Contao.
 *
 * (c) DIGIWERFT GmbH
 *
 * @license MIT
 */

namespace Digiwerft\ContaoEr24CustomSections\EventListener\DataContainer;

use Contao\DataContainer;
use Contao\StringUtil;
use Digiwerft\ContaoEr24CustomSections\LegalText\LegalTextDocument;
use Doctrine\DBAL\Connection;
use Fenepedia\ContaoErecht24Rechtstexte\Controller\ContentElement\LegalTextElementController;

/**
 * Provides the legal texts and their headings as options for the custom section element.
 */
class CustomSectionFieldsListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<int, string>
     */
    public function onTargetOptions(?DataContainer $dc = null): array
    {
        $options = [];

        foreach ($this->findLegalTexts($dc) as $legalText) {
            $type = $GLOBALS['TL_LANG']['tl_content']['er24TextTypes'][$legalText['er24Type']] ?? $legalText['er24Type'];
            $options[(int) $legalText['id']] = sprintf('%s (ID %s)', $type, $legalText['id']);
        }

        return $options;
    }

    /**
     * Preselects the legal text if there is only one in the same article.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function onTargetLoad($value, ?DataContainer $dc = null)
    {
        if ((int) $value > 0) {
            return $value;
        }

        $legalTexts = $this->findLegalTexts($dc);

        return 1 === \count($legalTexts) ? (int) $legalTexts[0]['id'] : $value;
    }

    /**
     * @return array<string, string>
     */
    public function onAnchorOptions(?DataContainer $dc = null): array
    {
        $record = $this->fetchRecord($dc);

        if (null === $record) {
            return [];
        }

        $targetId = (int) $this->onTargetLoad($record['er24CsTarget'], $dc);
        $html = $targetId ? $this->connection->fetchOne('SELECT er24Html FROM tl_content WHERE id = ? AND type = ?', [$targetId, LegalTextElementController::TYPE]) : '';
        $options = [];

        // The select menu does not escape option labels
        foreach ((new LegalTextDocument((string) $html))->getAnchors() as $heading) {
            $options[$heading->key] = StringUtil::specialchars($heading->getTrailLabel());
        }

        // Keep the stored anchor selectable if eRecht24 removed or renamed the heading
        $current = (string) $record['er24CsAnchor'];

        if ('' !== $current && !isset($options[$current])) {
            $options = [$current => sprintf($GLOBALS['TL_LANG']['tl_content']['er24CsAnchorGone'], StringUtil::specialchars($current))] + $options;
        }

        return $options;
    }

    /**
     * @return list<array{id: int|string, er24Type: string}>
     */
    private function findLegalTexts(?DataContainer $dc): array
    {
        $record = $this->fetchRecord($dc);

        if (null === $record) {
            return [];
        }

        $ptable = $record['ptable'] ?: 'tl_article';
        $ptableCondition = 'tl_article' === $ptable ? "(ptable = ? OR ptable = '')" : 'ptable = ?';

        return $this->connection->fetchAllAssociative(
            "SELECT id, er24Type FROM tl_content WHERE pid = ? AND $ptableCondition AND type = ? ORDER BY sorting",
            [$record['pid'], $ptable, LegalTextElementController::TYPE],
        );
    }

    /**
     * @return array{pid: int|string, ptable: string, er24CsTarget: int|string, er24CsAnchor: string}|null
     */
    private function fetchRecord(?DataContainer $dc): ?array
    {
        if (null === $dc || !$dc->id) {
            return null;
        }

        $record = $this->connection->fetchAssociative('SELECT pid, ptable, er24CsTarget, er24CsAnchor FROM tl_content WHERE id = ?', [$dc->id]);

        return false === $record ? null : $record;
    }
}
