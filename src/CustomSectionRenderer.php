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

use Contao\ContentModel;
use Contao\FrontendTemplate;
use Contao\StringUtil;

/**
 * Renders a custom section with the markup eRecht24 uses (plain heading followed by the text).
 */
class CustomSectionRenderer
{
    public const TEMPLATE = 'er24_custom_section';

    public function render(ContentModel $section, int $level): string
    {
        $template = new FrontendTemplate(self::TEMPLATE);
        $template->setData($section->row());
        $template->hl = 'h'.max(2, min(6, $level));
        $template->headline = $section->er24CsHeadline;
        $template->text = StringUtil::encodeEmail((string) $section->text);

        return $template->parse();
    }
}
