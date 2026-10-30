<?php

declare(strict_types=1);

/*
 * This file is part of the eRecht24 custom sections extension for Contao.
 *
 * (c) DIGIWERFT GmbH
 *
 * @license MIT
 */

namespace Digiwerft\ContaoEr24CustomSections\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\StringUtil;
use Contao\System;
use Digiwerft\ContaoEr24CustomSections\CustomSectionRenderer;
use Digiwerft\ContaoEr24CustomSections\LegalText\LegalTextDocument;
use Fenepedia\ContaoErecht24Rechtstexte\Controller\ContentElement\LegalTextElementController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A custom section for an eRecht24 legal text. The element itself renders nothing in the
 * front end, the section is inserted into the legal text by the InjectCustomSectionsListener.
 * In the back end it shows a preview including where the section will be inserted.
 */
class CustomSectionElementController extends AbstractContentElementController
{
    public const TYPE = 'er24_custom_section';

    public function __construct(
        private readonly CustomSectionRenderer $renderer,
        private readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    /**
     * The template is not typed, because Contao 4 passes a Template and Contao 5 a FragmentTemplate.
     *
     * @param object $template
     */
    protected function getResponse($template, ContentModel $model, Request $request): Response
    {
        if (!$this->scopeMatcher->isBackendRequest($request)) {
            return new Response();
        }

        System::loadLanguageFile('tl_content');

        $lang = $GLOBALS['TL_LANG']['tl_content'];
        $anchor = (string) $model->er24CsAnchor;
        $target = ContentModel::findByPk((int) $model->er24CsTarget);
        $level = LegalTextDocument::levelForKey($anchor);

        if (null === $target || LegalTextElementController::TYPE !== $target->type) {
            $status = sprintf('<p class="tl_error">%s</p>', $lang['er24CsTargetMissing']);
        } elseif (empty($target->er24Html)) {
            $status = sprintf('<p class="tl_info">%s</p>', sprintf($lang['er24CsTargetNotLoaded'], $target->id));
        } elseif (null === ($heading = (new LegalTextDocument((string) $target->er24Html))->resolve($anchor))) {
            $status = sprintf('<p class="tl_error">%s</p>', sprintf($lang['er24CsAnchorNotFound'], $model->er24CsHeadline, $model->id, StringUtil::specialchars($anchor)));
        } else {
            $level = $heading->level;
            $status = sprintf(
                '<p class="tl_help">%s</p>',
                sprintf($lang['er24CsInsertedAt'], $lang['er24CsPositions'][$model->er24CsPosition] ?? $model->er24CsPosition, StringUtil::specialchars($heading->getTrailLabel()), $target->id),
            );

            // Matched by name only, e.g. because eRecht24 renamed the parent chapter
            if ($heading->key !== $anchor) {
                $status .= sprintf('<p class="tl_info">%s</p>', $lang['er24CsAnchorMoved']);
            }
        }

        return new Response($status.$this->renderer->render($model, $level));
    }
}
