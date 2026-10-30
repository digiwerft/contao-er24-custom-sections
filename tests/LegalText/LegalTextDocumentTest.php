<?php

declare(strict_types=1);

/*
 * This file is part of the eRecht24 custom sections extension for Contao.
 *
 * (c) DIGIWERFT GmbH
 *
 * @license MIT
 */

namespace Digiwerft\ContaoEr24CustomSections\Tests\LegalText;

use Digiwerft\ContaoEr24CustomSections\LegalText\Heading;
use Digiwerft\ContaoEr24CustomSections\LegalText\LegalTextDocument;
use PHPUnit\Framework\TestCase;

class LegalTextDocumentTest extends TestCase
{
    private const RECIPIENTS = 'allgemeine-hinweise-und-pflichtinformationen/empfänger-von-personenbezogenen-daten';

    public function testIndexesAllHeadingsBelowTheDocumentTitle(): void
    {
        $document = new LegalTextDocument($this->getFixture());

        $this->assertSame(
            [
                'datenschutz-auf-einen-blick',
                'datenschutz-auf-einen-blick/allgemeine-hinweise',
                'hosting',
                'hosting/mittwald',
                'hosting/mittwald/auftragsverarbeitung',
                'allgemeine-hinweise-und-pflichtinformationen',
                'allgemeine-hinweise-und-pflichtinformationen/datenschutz',
                self::RECIPIENTS,
                'allgemeine-hinweise-und-pflichtinformationen/widerruf-ihrer-einwilligung-zur-datenverarbeitung',
                'datenerfassung-auf-dieser-website',
                'datenerfassung-auf-dieser-website/einwilligung-mit-cookiebot',
                'datenerfassung-auf-dieser-website/einwilligung-mit-cookiebot/auftragsverarbeitung',
            ],
            array_map(static fn (Heading $heading): string => $heading->key, $document->getAnchors()),
        );
    }

    public function testBuildsReadableLabelsWithoutSoftHyphens(): void
    {
        $heading = (new LegalTextDocument($this->getFixture()))->resolve(self::RECIPIENTS);

        $this->assertNotNull($heading);
        $this->assertSame(3, $heading->level);
        $this->assertSame('Empfänger von personenbezogenen Daten', $heading->label);
        $this->assertSame('3. Allgemeine Hinweise und Pflichtinformationen › Empfänger von personenbezogenen Daten', $heading->getTrailLabel());
    }

    public function testInsertsAfterTheSectionInFrontOfTheNextSibling(): void
    {
        $html = $this->insert($this->getFixture(), self::RECIPIENTS, LegalTextDocument::POSITION_AFTER, '<h3>Meldung zur Kurabgabe</h3>');

        $this->assertOrder($html, ['>Empf&auml;nger von', '<h3>Meldung zur Kurabgabe</h3>', '>Widerruf Ihrer Einwilligung']);
    }

    public function testInsertsInFrontOfTheHeading(): void
    {
        $key = 'allgemeine-hinweise-und-pflichtinformationen/widerruf-ihrer-einwilligung-zur-datenverarbeitung';
        $html = $this->insert($this->getFixture(), $key, LegalTextDocument::POSITION_BEFORE, '<h3>Meldung zur Kurabgabe</h3>');

        $this->assertOrder($html, ['Im Rahmen unserer Gesch', '<h3>Meldung zur Kurabgabe</h3>', '>Widerruf Ihrer Einwilligung']);
    }

    public function testInsertsAfterTheSubSectionsOfASection(): void
    {
        $html = $this->insert($this->getFixture(), 'hosting/mittwald', LegalTextDocument::POSITION_AFTER, '<h3>Backup</h3>');

        $this->assertOrder($html, ['Vertrag &uuml;ber Auftragsverarbeitung', '<h3>Backup</h3>', '<h2>3. Allgemeine Hinweise']);
    }

    public function testInsertsAfterTheLastSectionInsideTheElementWrapper(): void
    {
        $html = $this->insert($this->getFixture(), 'datenerfassung-auf-dieser-website', LegalTextDocument::POSITION_AFTER, '<h2>Kurabgabe</h2>');

        $this->assertOrder($html, ['Kontakt:', '<h2>Kurabgabe</h2>', '</div>']);
        $this->assertStringEndsWith("</div>\n", $html);
    }

    public function testUsesTheEndOfTheHtmlIfThereIsNoWrapper(): void
    {
        $html = '<h2>1. Kapitel</h2><p>Text</p>';

        $this->assertSame(\strlen($html), (new LegalTextDocument($html))->getEndOffset());
    }

    public function testIgnoresChapterRenumbering(): void
    {
        $fixture = str_replace('<h2>3. Allgemeine Hinweise', '<h2>5. Allgemeine Hinweise', $this->getFixture());
        $heading = (new LegalTextDocument($fixture))->resolve(self::RECIPIENTS);

        $this->assertNotNull($heading);
        $this->assertSame(self::RECIPIENTS, $heading->key);
    }

    public function testFindsAUniqueHeadingByNameIfItsChapterWasRenamed(): void
    {
        $fixture = str_replace('3. Allgemeine Hinweise und Pflicht&shy;informationen', '3. Pflichtangaben', $this->getFixture());
        $heading = (new LegalTextDocument($fixture))->resolve(self::RECIPIENTS);

        $this->assertNotNull($heading);
        $this->assertSame('pflichtangaben/empfänger-von-personenbezogenen-daten', $heading->key);
    }

    public function testDoesNotGuessIfTheHeadingNameIsAmbiguous(): void
    {
        $document = new LegalTextDocument($this->getFixture());

        $this->assertNull($document->resolve('umbenanntes-kapitel/auftragsverarbeitung'));
        $this->assertNotNull($document->resolve('hosting/mittwald/auftragsverarbeitung'));
    }

    public function testReturnsNullForEmptyOrUnknownAnchors(): void
    {
        $document = new LegalTextDocument($this->getFixture());

        $this->assertNull($document->resolve(''));
        $this->assertNull($document->resolve('gibt-es-nicht'));
    }

    public function testLeavesTheLegalTextByteForByteUntouched(): void
    {
        $fixture = $this->getFixture();
        $snippet = '<h3>Meldung zur Kurabgabe</h3>';
        $html = $this->insert($fixture, self::RECIPIENTS, LegalTextDocument::POSITION_AFTER, $snippet);

        $this->assertSame($fixture, str_replace("\n".$snippet."\n", '', $html));
        $this->assertStringContainsString('href="mailto:{{email::datenschutz@example.org}}"', $html);
    }

    public function testKeepsTheOrderOfSnippetsAtTheSameOffset(): void
    {
        $document = new LegalTextDocument($this->getFixture());
        $heading = $document->resolve(self::RECIPIENTS);
        $offset = $document->getInsertOffset($heading, LegalTextDocument::POSITION_AFTER);

        $html = $document->insert([[$offset, '<h3>Erster</h3>'], [$offset, '<h3>Zweiter</h3>']]);

        $this->assertOrder($html, ['<h3>Erster</h3>', '<h3>Zweiter</h3>', '>Widerruf Ihrer Einwilligung']);
    }

    /**
     * @dataProvider levelProvider
     */
    public function testDerivesTheLevelOfUnresolvableAnchorsFromTheirDepth(string $key, int $level): void
    {
        $this->assertSame($level, LegalTextDocument::levelForKey($key));
    }

    public static function levelProvider(): iterable
    {
        yield ['kapitel', 2];
        yield ['kapitel/abschnitt', 3];
        yield ['kapitel/abschnitt/unterabschnitt', 4];
        yield ['a/b/c/d/e/f/g', 6];
    }

    private function insert(string $html, string $key, string $position, string $snippet): string
    {
        $document = new LegalTextDocument($html);
        $heading = $document->resolve($key);

        $this->assertNotNull($heading, sprintf('Anchor "%s" not found.', $key));

        return $document->insert([[$document->getInsertOffset($heading, $position), $snippet]]);
    }

    /**
     * @param list<string> $needles
     */
    private function assertOrder(string $html, array $needles): void
    {
        $positions = array_map(
            function (string $needle) use ($html): int {
                $position = strpos($html, $needle);
                $this->assertNotFalse($position, sprintf('"%s" not found.', $needle));

                return $position;
            },
            $needles,
        );

        $sorted = $positions;
        sort($sorted);

        $this->assertSame($sorted, $positions, 'Unexpected order: '.implode(' → ', $needles));
    }

    private function getFixture(): string
    {
        return file_get_contents(__DIR__.'/../Fixtures/privacy_policy.html');
    }
}
