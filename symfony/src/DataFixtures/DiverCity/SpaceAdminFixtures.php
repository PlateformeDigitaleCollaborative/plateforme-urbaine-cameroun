<?php

namespace App\DataFixtures\DiverCity;

use App\Entity\DiverCity\Space;
use App\Entity\DiverCity\SpaceAdmin;
use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Désigne editor@test.com comme administrateur DiverCity de l'espace
 * principal (droit MANAGE_SPACE sur SpaceScopedVoter).
 *
 * Prérequis : mêmes utilisateurs Alice que SpaceFixtures.
 */
class SpaceAdminFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Space $space */
        $space = $this->getReference(SpaceFixtures::REFERENCE_MAIN_SPACE, Space::class);

        $editor = $manager->getRepository(User::class)->findOneBy(['email' => 'editor@test.com']);

        if (null === $editor) {
            return;
        }

        $spaceAdmin = new SpaceAdmin();
        $spaceAdmin->setSpace($space);
        $spaceAdmin->setUser($editor);

        $manager->persist($spaceAdmin);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SpaceFixtures::class,
        ];
    }
}
