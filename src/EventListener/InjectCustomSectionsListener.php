<?php

declare(strict_types=1);

/*
 * This file is part of the eRecht24 custom sections extension for Contao.
 *
 * (c) DIGIWERFT GmbH
 *
 * @license MIT
 */

namespace Digiwerft\ContaoEr24CustomSections\EventListener;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\StringUtil;
use Contao\System;
use Digiwerft\ContaoEr24CustomSections\Controller\ContentElement\CustomSectionElementController;
use Digiwerft\ContaoEr24CustomSections\CustomSectionRenderer;
use Digiwerft\ContaoEr24CustomSections\LegalText\LegalTextDocument;
use Fenepedia\ContaoErecht24Rechtstexte\Controller\ContentElement\LegalTextElementController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Inserts the custom sections into the rendered eRecht24 legal text. This happens after the
 * legal text was fetched and cached, so pushes from eRecht24 keep working as before.
 */
class InjectCustomSectionsListener
{
    public function __construct(
        private readonly CustomSectionRenderer $renderer,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly RequestStack $requestStack,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @param object $element
     */
    public function __invoke(ContentModel $model, string $buffer, $element): string
    {
        if (LegalTextElementController::TYPE !== $model->type || '' === trim($buffer)) {
            return $buffer;
        }

        $sections = $this->findSections($model);

        if (empty($sections)) {
            return $buffer;
        }

        $document = new LegalTextDocument($buffer);
        $insertions = [];
        $missing = [];

        foreach ($sections as $section) {
            $heading = $document->resolve((string) $section->er24CsAnchor);

            // Never drop a section silently: append it at the end if its anchor is gone
            if (null === $heading) {
                $missing[] = $section;
                $insertions[] = [$document->getEndOffset(), $this->renderer->render($section, LegalTextDocument::levelForKey((string) $section->er24CsAnchor))];
                continue;
            }

            $insertions[] = [$document->getInsertOffset($heading, (string) $section->er24CsPosition), $this->renderer->render($section, $heading->level)];
        }

        $buffer = $document->insert($insertions);

        if (!empty($missing)) {
            $buffer = $this->reportMissing($model, $missing, $buffer);
        }

        return $buffer;
    }

    /**
     * @return list<ContentModel>
     */
    private function findSections(ContentModel $model): array
    {
        $elements = ContentModel::findPublishedByPidAndTable((int) $model->pid, $model->ptable ?: 'tl_article');

        if (null === $elements) {
            return [];
        }

        $sections = [];

        foreach ($elements as $element) {
            if (
                CustomSectionElementController::TYPE === $element->type
                && (int) $element->er24CsTarget === (int) $model->id
                && Controller::isVisibleElement($element)
            ) {
                $sections[] = $element;
            }
        }

        return $sections;
    }

    /**
     * @param list<ContentModel> $missing
     */
    private function reportMissing(ContentModel $model, array $missing, string $buffer): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request && $this->scopeMatcher->isBackendRequest($request)) {
            System::loadLanguageFile('tl_content');

            $messages = array_map(
                static fn (ContentModel $section): string => sprintf(
                    '<p class="tl_error">%s</p>',
                    sprintf($GLOBALS['TL_LANG']['tl_content']['er24CsAnchorNotFound'], $section->er24CsHeadline, $section->id, StringUtil::specialchars((string) $section->er24CsAnchor)),
                ),
                $missing,
            );

            return implode('', $messages).$buffer;
        }

        foreach ($missing as $section) {
            $this->logger?->error(
                sprintf('eRecht24 custom section ID %s: anchor "%s" not found in legal text ID %s, appended at the end instead.', $section->id, $section->er24CsAnchor, $model->id),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR)],
            );
        }

        return $buffer;
    }
}
