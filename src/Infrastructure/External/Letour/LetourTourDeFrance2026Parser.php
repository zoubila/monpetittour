<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Letour;

use App\Application\DTO\ImportedStage;
use App\Application\DTO\ImportedStageResult;

final class LetourTourDeFrance2026Parser
{
    private const BASE_URL = 'https://www.letour.fr';

    /**
     * @return list<ImportedStage>
     */
    public function parseStages(string $html): array
    {
        $document = $this->document($html);
        $xpath = new \DOMXPath($document);
        $rows = $xpath->query(
            '//section[contains(concat(" ", normalize-space(@class), " "), " generalRace ")]//tbody/tr',
        );

        $stages = [];
        foreach ($rows ?: [] as $row) {
            if (!$row instanceof \DOMElement) {
                continue;
            }

            $cells = $this->cells($row);
            if (count($cells) < 5 || preg_match('/^\d+$/', $cells[0]) !== 1) {
                continue;
            }

            [$startLocation, $finishLocation] = $this->splitRoute($cells[3]);
            $stages[] = new ImportedStage(
                (int) $cells[0],
                $startLocation,
                $finishLocation,
                (float) str_replace(',', '.', str_replace(' km', '', $cells[4])),
                0,
                null,
            );
        }

        return $stages;
    }

    public function parseStageProfilePath(string $html): ?string
    {
        preg_match_all('/<img\b[^>]*>/i', $html, $imageMatches);

        foreach ($imageMatches[0] as $image) {
            if (stripos($image, 'tdf26-profils-web') === false) {
                continue;
            }

            if (preg_match('/\bdata-src=["\'](?<source>[^"\']+)["\']/i', $image, $sourceMatches) === 1) {
                return $this->firstLazyImageSource($sourceMatches['source']);
            }
        }

        return null;
    }

    /**
     * @return list<ImportedStageResult>
     */
    public function parseStageResults(string $html): array
    {
        $document = $this->document($html);
        $xpath = new \DOMXPath($document);
        $tables = $xpath->query(
            '//table[.//th[contains(normalize-space(.), "Coureur")]]',
        );

        $results = [];
        foreach ($tables ?: [] as $table) {
            if (!$table instanceof \DOMElement) {
                continue;
            }

            foreach ($this->stageResultRows($table) as $row) {
                $cells = $this->cells($row);
                if (count($cells) < 5 || preg_match('/^\d+$/', $cells[0]) !== 1) {
                    continue;
                }

                $timeInSeconds = $this->letourDurationToSeconds($cells[4]);
                if ($timeInSeconds === null) {
                    continue;
                }

                $gapInSeconds = $this->letourGapToSeconds($cells[5] ?? '-');
                if ($gapInSeconds === null) {
                    continue;
                }

                $results[] = new ImportedStageResult(
                    (int) $cells[0],
                    $this->riderName($row, $cells[1]),
                    str_replace('|', '', $cells[3]),
                    $timeInSeconds,
                    $gapInSeconds,
                );
            }
        }

        return $results;
    }

    public function parseStageIndividualResultsPath(string $html): ?string
    {
        preg_match_all('/data-ajax-stack\s*=\s*(?<stack>\{[^}]+})/i', $html, $matches);

        foreach ($matches['stack'] as $encodedStack) {
            $decodedStack = html_entity_decode($encodedStack, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $stack = json_decode($decodedStack, true);

            if (!is_array($stack) || !isset($stack['ite']) || !is_string($stack['ite'])) {
                continue;
            }

            return $this->firstLazyImageSource($stack['ite']);
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function parseAbandonedRiderNames(string $html, int $stageNumber): array
    {
        $document = $this->document($html);
        $xpath = new \DOMXPath($document);
        $stageNodes = $xpath->query(sprintf('//*[@id="stage-%d"]', $stageNumber));

        if ($stageNodes === false || $stageNodes->length === 0) {
            return [];
        }

        $stageNode = $stageNodes->item(0);
        if (!$stageNode instanceof \DOMElement || str_contains($stageNode->getAttribute('class'), 'no-withdraws')) {
            return [];
        }

        $riderNames = [];
        foreach ($stageNode->getElementsByTagName('a') as $link) {
            $href = $link->getAttribute('href');
            if (!str_contains($href, '/fr/coureur/')) {
                continue;
            }

            $slugName = $this->riderNameFromLetourRiderPath($href);
            $riderNames[] = $slugName !== null ? $slugName : $this->cleanRiderName($link->textContent);
        }

        return array_values(array_unique(array_filter($riderNames, static fn (string $name): bool => $name !== '')));
    }

    private function document(string $html): \DOMDocument
    {
        $document = new \DOMDocument();
        $previousUseErrors = libxml_use_internal_errors(true);
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);

        return $document;
    }

    /**
     * @return list<string>
     */
    private function cells(\DOMElement $row): array
    {
        $cells = [];

        foreach ($row->childNodes as $childNode) {
            if (!$childNode instanceof \DOMElement || !in_array($childNode->tagName, ['td', 'th'], true)) {
                continue;
            }

            $cells[] = trim(preg_replace('/\s+/', ' ', $childNode->textContent) ?? $childNode->textContent);
        }

        return $cells;
    }

    /**
     * @return list<\DOMElement>
     */
    private function stageResultRows(\DOMElement $table): array
    {
        $rows = [];

        foreach ($table->getElementsByTagName('tr') as $row) {
            if (str_contains($row->getAttribute('class'), 'rankingTables__row')) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitRoute(string $route): array
    {
        $parts = explode('>', $route, 2);

        if (count($parts) !== 2) {
            return [trim($route), trim($route)];
        }

        return [trim($parts[0]), trim($parts[1])];
    }

    private function firstLazyImageSource(string $source): string
    {
        $source = html_entity_decode($source, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $source = explode('|', $source, 2)[0];

        if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://')) {
            return $source;
        }

        if (str_starts_with($source, '//')) {
            return 'https:' . $source;
        }

        if (str_starts_with($source, '/')) {
            return self::BASE_URL . $source;
        }

        return self::BASE_URL . '/' . $source;
    }

    private function cleanRiderName(string $riderName): string
    {
        $riderName = str_replace("\u{00a0}", ' ', $riderName);
        $riderName = preg_replace('/\s+/', ' ', $riderName) ?? $riderName;

        return trim($riderName);
    }

    private function riderName(\DOMElement $row, string $fallbackName): string
    {
        foreach ($row->getElementsByTagName('a') as $link) {
            if (!str_contains($link->getAttribute('class'), 'rankingTables__row__profile--name')) {
                continue;
            }

            $riderName = $this->riderNameFromLetourRiderPath($link->getAttribute('href'));

            if ($riderName !== null) {
                return $riderName;
            }
        }

        return $this->cleanRiderName($fallbackName);
    }

    private function riderNameFromLetourRiderPath(string $path): ?string
    {
        $pathParts = explode('/', trim($path, '/'));
        $slug = end($pathParts);

        if ($slug === '') {
            return null;
        }

        return str_replace('-', ' ', $slug);
    }

    private function letourDurationToSeconds(string $duration): ?int
    {
        if (preg_match('/(?<hours>\d+)h\s*(?<minutes>\d+)\'\s*(?<seconds>\d+)\'\'/', $duration, $matches) !== 1) {
            return null;
        }

        return ((int) $matches['hours'] * 3_600) + ((int) $matches['minutes'] * 60) + (int) $matches['seconds'];
    }

    private function letourGapToSeconds(string $gap): ?int
    {
        $gap = trim(str_replace("\u{00a0}", ' ', $gap));

        if ($gap === '' || $gap === '-') {
            return 0;
        }

        return $this->letourDurationToSeconds(ltrim($gap, '+ '));
    }
}
