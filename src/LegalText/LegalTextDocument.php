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
 * Indexes the headings of an eRecht24 legal text and inserts HTML at heading
 * boundaries without re-serializing the document. The legal text itself stays
 * byte-for-byte untouched, only the custom sections are spliced in.
 */
final class LegalTextDocument
{
    public const POSITION_BEFORE = 'before';
    public const POSITION_AFTER = 'after';

    /**
     * All headings (h1–h6) in document order.
     *
     * @var list<Heading>
     */
    private array $headings = [];

    public function __construct(private readonly string $html)
    {
        preg_match_all('#<h([1-6])\b[^>]*>(.*?)</h\1\s*>#is', $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        /** @var array<int, Heading> $parents */
        $parents = [];

        foreach ($matches as $match) {
            $level = (int) $match[1][0];
            $label = self::toPlainText($match[2][0]);

            // Headings on the same or a deeper level are no parents anymore
            $parents = array_filter($parents, static fn (int $parentLevel): bool => $parentLevel < $level, ARRAY_FILTER_USE_KEY);

            // The h1 is the document title and not part of the anchor path
            $trail = 1 === $level ? [] : array_values(array_filter($parents, static fn (Heading $parent): bool => $parent->level > 1));
            $slug = self::slugify($label);
            $key = '' === $slug ? '' : implode('/', [...array_map(static fn (Heading $parent): string => $parent->getSlug(), $trail), $slug]);

            $heading = new Heading($level, $label, $key, $match[0][1], array_map(static fn (Heading $parent): string => $parent->label, $trail));

            $parents[$level] = $heading;
            $this->headings[] = $heading;
        }
    }

    /**
     * Returns the headings a custom section can be anchored to (everything below the document title).
     *
     * @return list<Heading>
     */
    public function getAnchors(): array
    {
        return array_values(array_filter($this->headings, static fn (Heading $heading): bool => $heading->level > 1 && '' !== $heading->key));
    }

    /**
     * Finds the heading for an anchor key. If the exact path no longer exists (e.g. the chapter
     * was renamed by eRecht24), a heading with the same name is used as long as it is unique.
     */
    public function resolve(string $key): ?Heading
    {
        if ('' === $key) {
            return null;
        }

        $anchors = $this->getAnchors();

        foreach ($anchors as $heading) {
            if ($heading->key === $key) {
                return $heading;
            }
        }

        $slug = Heading::leafOf($key);
        $candidates = array_values(array_filter($anchors, static fn (Heading $heading): bool => $heading->getSlug() === $slug));

        return 1 === \count($candidates) ? $candidates[0] : null;
    }

    /**
     * "before" inserts directly in front of the heading, "after" behind the whole section
     * including its sub sections (i.e. in front of the next heading on the same or a higher level).
     */
    public function getInsertOffset(Heading $heading, string $position): int
    {
        if (self::POSITION_BEFORE === $position) {
            return $heading->offset;
        }

        foreach ($this->headings as $next) {
            if ($next->offset > $heading->offset && $next->level <= $heading->level) {
                return $next->offset;
            }
        }

        return $this->getEndOffset();
    }

    /**
     * Returns the end of the legal text. If the HTML is wrapped in the element's <div>, the
     * offset points in front of its closing tag so the inserted HTML stays inside the element.
     */
    public function getEndOffset(): int
    {
        if (preg_match('#</div>\s*$#i', $this->html, $match, PREG_OFFSET_CAPTURE)) {
            return $match[0][1];
        }

        return \strlen($this->html);
    }

    /**
     * Inserts the given HTML snippets. Snippets with the same offset keep their order.
     *
     * @param list<array{0: int, 1: string}> $insertions List of [offset, html]
     */
    public function insert(array $insertions): string
    {
        $byOffset = [];

        foreach ($insertions as [$offset, $html]) {
            $byOffset[$offset][] = $html;
        }

        // Insert from the end so the remaining offsets stay valid
        krsort($byOffset);

        $result = $this->html;

        foreach ($byOffset as $offset => $snippets) {
            $result = substr_replace($result, "\n".implode("\n", $snippets)."\n", $offset, 0);
        }

        return $result;
    }

    /**
     * Derives the heading level of an anchor key from its depth (used if the anchor cannot be resolved).
     */
    public static function levelForKey(string $key): int
    {
        return min(6, substr_count($key, '/') + 2);
    }

    public static function toPlainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // eRecht24 uses soft hyphens (&shy;) in long words
        $text = preg_replace('/[\x{00AD}\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);

        return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $text));
    }

    /**
     * Strips the chapter numbering, so renumbered chapters ("3." → "4.") keep their anchor.
     */
    public static function slugify(string $label): string
    {
        $text = preg_replace('/^\d+(?:\.\d+)*\.\s+/u', '', $label);
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($text, 'UTF-8'));

        return trim($text, '-');
    }
}
