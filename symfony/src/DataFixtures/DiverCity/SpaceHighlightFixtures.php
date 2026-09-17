<?php

namespace App\DataFixtures\DiverCity;

use App\DataFixtures\Factory\FixtureFileFactory;
use App\Entity\DiverCity\Space;
use App\Entity\DiverCity\SpaceHighlight;
use App\Entity\DiverCity\SpaceStatistic;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Temps forts de l'espace DiverCity : un bloc par (année, semestre), avec
 * son rapport d'activité (PDF généré à la volée) et ses statistiques
 * manuelles affichées sur la fiche de l'espace.
 *
 * Les années sont calculées à partir de l'année courante pour que le jeu de
 * données reste cohérent quelle que soit la date de chargement.
 */
class SpaceHighlightFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly FixtureFileFactory $fileFactory)
    {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Space $space */
        $space = $this->getReference(SpaceFixtures::REFERENCE_MAIN_SPACE, Space::class);

        $currentYear = (int) date('Y');

        foreach ($this->highlightsData($currentYear) as $data) {
            $highlight = new SpaceHighlight();
            $highlight->setSpace($space);
            $highlight->setYear($data['year']);
            $highlight->setSemester($data['semester']);

            $report = $this->fileFactory->createGeneratedPdf(
                sprintf('rapport-divercity-%d-s%d.pdf', $data['year'], $data['semester']),
                [
                    "Rapport d'activité — DiverCity Space",
                    sprintf('%d — %s semestre', $data['year'], 1 === $data['semester'] ? '1er' : '2e'),
                    '',
                    'Tiers-lieu DiverCity — Golf Bastos, Yaoundé',
                    'Plateforme Nationale des Acteurs de l\'Urbain (PNAU)',
                    '',
                    'Document de démonstration généré par les fixtures.',
                ]
            );

            $manager->persist($report);
            $highlight->setReport($report);

            foreach ($data['statistics'] as $position => $statistic) {
                $spaceStatistic = new SpaceStatistic();
                $spaceStatistic->setLabel($statistic[0]);
                $spaceStatistic->setValue($statistic[1]);
                $spaceStatistic->setPosition($position);

                $highlight->addStatistic($spaceStatistic);
                $manager->persist($spaceStatistic);
            }

            $manager->persist($highlight);
        }

        $manager->flush();
    }

    /**
     * @return array<int, array{year: int, semester: int, statistics: array<int, array{0: string, 1: string}>}>
     */
    private function highlightsData(int $currentYear): array
    {
        return [
            [
                'year' => $currentYear - 1,
                'semester' => 1,
                'statistics' => [
                    ['Évènements accueillis', '18'],
                    ['Participants cumulés', '640'],
                    ['Structures partenaires', '27'],
                    ['Taux d\'occupation', '54 %'],
                ],
            ],
            [
                'year' => $currentYear - 1,
                'semester' => 2,
                'statistics' => [
                    ['Évènements accueillis', '24'],
                    ['Participants cumulés', '910'],
                    ['Structures partenaires', '35'],
                    ['Ateliers de co-création', '9'],
                    ['Taux d\'occupation', '63 %'],
                ],
            ],
            [
                'year' => $currentYear,
                'semester' => 1,
                'statistics' => [
                    ['Évènements accueillis', '31'],
                    ['Participants cumulés', '1 280'],
                    ['Structures partenaires', '42'],
                    ['Ateliers de co-création', '12'],
                    ['Taux d\'occupation', '71 %'],
                ],
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            SpaceFixtures::class,
        ];
    }
}