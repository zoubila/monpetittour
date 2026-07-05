<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Application\DTO\ImportedRider;
use App\Application\DTO\RiderDetails;
use App\Application\DTO\RiderListItem;
use App\Application\Repository\RiderReadRepositoryInterface;
use App\Application\Repository\RiderWriteRepositoryInterface;
use App\Application\Service\CountryFlagResolver;
use App\Application\Service\RiderNameNormalizer;
use App\Domain\Entity\Rider;
use App\Infrastructure\Doctrine\DoctrineFantasyCatalog;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RiderRecord>
 */
final class RiderRecordRepository extends ServiceEntityRepository implements RiderReadRepositoryInterface, RiderWriteRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $registry,
        private readonly CountryFlagResolver $countryFlags,
        private readonly RiderNameNormalizer $riderNameNormalizer,
    ) {
        parent::__construct($registry, RiderRecord::class);
        $this->entityManager = $this->getEntityManager();
    }

    /**
     * @return list<RiderListItem>
     */
    public function listRiders(): array
    {
        return array_map(
            fn (RiderRecord $rider): RiderListItem => new RiderListItem(
                $rider->id(),
                $rider->slug(),
                $rider->name(),
                $rider->realTeam(),
                $rider->nationality(),
                $this->countryFlags->forNationality($rider->nationality()),
                $rider->marketValueInEuros(),
                $rider->specialty()?->value,
            ),
            $this->findAllOrderedByName(),
        );
    }

    public function riderDetailsBySlug(string $slug): ?RiderDetails
    {
        $rider = $this->findOneBySlug($slug);

        if (!$rider instanceof RiderRecord) {
            return null;
        }

        return new RiderDetails(
            $rider->slug(),
            $rider->name(),
            $rider->realTeam(),
            $rider->nationality(),
            $this->countryFlags->forNationality($rider->nationality()),
            $rider->marketValueInEuros(),
            $rider->specialty()?->value,
        );
    }

    /**
     * @return list<RiderRecord>
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('rider')
            ->orderBy('rider.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?RiderRecord
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findOneByImportedName(string $name): ?RiderRecord
    {
        $importedNames = $this->riderNameNormalizer->comparableNames($name);

        foreach ($this->findAllOrderedByName() as $rider) {
            if ($this->riderNameNormalizer->namesAreCompatible($name, $rider->name())) {
                return $rider;
            }

            if ($this->riderNameNormalizer->abbreviatedNameMatches($name, $rider->name())) {
                return $rider;
            }

            foreach ($this->riderNameNormalizer->comparableNames($rider->name()) as $knownName) {
                if (in_array($knownName, $importedNames, true)) {
                    return $rider;
                }
            }
        }

        return null;
    }

    /**
     * @param list<int> $ids
     * @return list<RiderRecord>
     */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return $this->createQueryBuilder('rider')
            ->andWhere('rider.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<int> $ids
     * @return list<Rider>
     */
    public function domainRidersByIds(array $ids): array
    {
        return array_map(
            static fn (RiderRecord $rider): Rider => DoctrineFantasyCatalog::toDomainRider($rider),
            $this->findByIds($ids),
        );
    }

    public function countRiders(): int
    {
        return $this->count([]);
    }

    public function save(RiderRecord $rider): void
    {
        $this->getEntityManager()->persist($rider);
    }

    /**
     * @param iterable<ImportedRider> $riders
     */
    public function replaceAllFromImport(iterable $riders): void
    {
        foreach ($riders as $rider) {
            $existingRider = $this->findOneBySlug($rider->slug);

            if ($existingRider instanceof RiderRecord) {
                $existingRider->updateFromImport(
                    $rider->slug,
                    $rider->name,
                    $rider->realTeam,
                    $rider->nationality,
                    $rider->marketValueInEuros,
                    $rider->specialty,
                );

                continue;
            }

            $matchingRider = $this->findOneByImportedName($rider->name);
            if ($matchingRider instanceof RiderRecord) {
                $matchingRider->updateFromImport(
                    $rider->slug,
                    $rider->name,
                    $rider->realTeam,
                    $rider->nationality,
                    $rider->marketValueInEuros,
                    $rider->specialty,
                );

                continue;
            }

            $this->entityManager->persist(
                new RiderRecord(
                    $rider->slug,
                    $rider->name,
                    $rider->realTeam,
                    $rider->nationality,
                    $rider->marketValueInEuros,
                    $rider->specialty,
                ),
            );
        }

        $this->entityManager->flush();
    }
}
