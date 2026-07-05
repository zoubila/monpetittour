<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Letour;

use App\Application\DTO\ImportedStage;
use App\Application\DTO\ImportedStageResult;
use App\Application\Port\TourDeFrance2026StageDataSourceInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class LetourTourDeFrance2026StageDataSource implements TourDeFrance2026StageDataSourceInterface
{
    private const SITE_URL = 'https://www.letour.fr';
    private const BASE_URL = 'https://www.letour.fr/fr';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LetourTourDeFrance2026Parser $parser,
    ) {
    }

    /**
     * @return list<ImportedStage>
     */
    public function stages(): array
    {
        $stages = [];

        foreach ($this->parser->parseStages($this->fetch('/parcours-general')) as $stage) {
            $stages[] = new ImportedStage(
                $stage->number,
                $stage->startLocation,
                $stage->finishLocation,
                $stage->distanceInKilometers,
                $stage->positiveElevationInMeters,
                $this->parser->parseStageProfilePath($this->fetch(sprintf('/etape-%d', $stage->number))),
            );
        }

        return $stages;
    }

    /**
     * @return list<ImportedStageResult>
     */
    public function stageResults(int $stageNumber): array
    {
        $rankingPage = $this->fetch(sprintf('/classements/etape-%d', $stageNumber));

        if ($stageNumber === 1) {
            return $this->parser->parseStageResults($rankingPage);
        }

        $stageIndividualResultsPath = $this->parser->parseStageIndividualResultsPath($rankingPage);
        if ($stageIndividualResultsPath === null) {
            throw new LetourScrapingUnavailable(sprintf(
                'Letour individual stage ranking was not found for stage %d.',
                $stageNumber,
            ));
        }

        return $this->parser->parseStageResults($this->fetch($stageIndividualResultsPath));
    }

    private function fetch(string $path): string
    {
        $response = $this->httpClient->request('GET', $this->url($path), [
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml',
                'User-Agent' => 'Mozilla/5.0 MonPetitTourBot/1.0',
            ],
            'timeout' => 20,
        ]);

        $html = $response->getContent(false);

        if ($response->getStatusCode() >= 400) {
            throw new LetourScrapingUnavailable(sprintf('Letour returned HTTP %d.', $response->getStatusCode()));
        }

        return $html;
    }

    private function url(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/fr/ajax/')) {
            return self::SITE_URL . $path;
        }

        return self::BASE_URL . $path;
    }
}
