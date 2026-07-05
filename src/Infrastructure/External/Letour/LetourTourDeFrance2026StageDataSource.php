<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Letour;

use App\Application\DTO\ImportedStage;
use App\Application\DTO\ImportedStageResult;
use App\Application\Port\TourDeFrance2026StageDataSourceInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class LetourTourDeFrance2026StageDataSource implements TourDeFrance2026StageDataSourceInterface
{
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
        return $this->parser->parseStageResults($this->fetch(sprintf('/classements/etape-%d', $stageNumber)));
    }

    private function fetch(string $path): string
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . $path, [
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
}
