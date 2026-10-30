<?php

declare(strict_types=1);

/*
 * This file is part of the eRecht24 custom sections extension for Contao.
 *
 * (c) DIGIWERFT GmbH
 *
 * @license MIT
 */

namespace Digiwerft\ContaoEr24CustomSections\LegalText;

/**
 * A heading inside an eRecht24 legal text.
 */
final class Heading
{
    /**
     * @param int          $level  Heading level (1–6)
     * @param string       $label  Plain text of the heading, including its numbering ("3. Allgemeine Hinweise …")
     * @param string       $key    Stable anchor key built from the slugs of all parent headings (level ≥ 2) and this heading
     * @param int          $offset Byte offset of the opening tag within the document
     * @param list<string> $trail  Labels of the parent headings (level ≥ 2)
     */
    public function __construct(
        public readonly int $level,
        public readonly string $label,
        public readonly string $key,
        public readonly int $offset,
        public readonly array $trail,
    ) {
    }

    public function getSlug(): string
    {
        return self::leafOf($this->key);
    }

    public function getTrailLabel(string $separator = ' › '): string
    {
        return implode($separator, [...$this->trail, $this->label]);
    }

    public static function leafOf(string $key): string
    {
        $pos = strrpos($key, '/');

        return false === $pos ? $key : substr($key, $pos + 1);
    }
}
