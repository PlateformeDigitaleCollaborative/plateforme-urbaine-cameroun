<?php

namespace App\DataFixtures\DiverCity;

use App\DataFixtures\Factory\FixtureFileFactory;
use App\Entity\DiverCity\Booking;
use App\Entity\DiverCity\BookingAttachment;
use App\Entity\DiverCity\EventActivityType;
use App\Entity\DiverCity\InformationSource;
use App\Entity\DiverCity\Notification;
use App\Entity\DiverCity\Space;
use App\Entity\DiverCity\Status;
use App\Entity\Resource;
use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Réservations de démonstration du tiers-lieu DiverCity.
 *
 * Couvre tous les statuts du workflow (en attente, en cours de traitement,
 * acceptée, refusée, annulée), des créneaux passés et à venir, les pièces
 * jointes obligatoires (ordre du jour + document ressource), les ressources
 * liées et les notifications associées.
 *
 * Les dates sont relatives à la date de chargement : les réservations
 * acceptées à venir alimentent le calendrier de disponibilités et le bloc
 * "Activités à venir" de la fiche de l'espace.
 */
class BookingFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_PREFIX = 'divercity_booking_';

    public function __construct(private readonly FixtureFileFactory $fileFactory)
    {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Space $space */
        $space = $this->getReference(SpaceFixtures::REFERENCE_MAIN_SPACE, Space::class);

        $userRepository = $manager->getRepository(User::class);
        $users = [
            'admin@test.com' => $userRepository->findOneBy(['email' => 'admin@test.com']),
            'editor@test.com' => $userRepository->findOneBy(['email' => 'editor@test.com']),
            'lambda@test.com' => $userRepository->findOneBy(['email' => 'lambda@test.com']),
        ];

        // Ressources (fixtures Alice) réparties entre les réservations : une
        // ressource ne peut être liée qu'à une seule réservation à la fois.
        $availableResources = $manager->getRepository(Resource::class)
            ->findBy(['isValidated' => true], ['name' => 'ASC'], 12);
        $resourceCursor = 0;

        foreach ($this->bookingsData() as $index => $data) {
            $user = $users[$data['userEmail']] ?? null;

            if (null === $user) {
                continue;
            }

            $status = $manager->getRepository(Status::class)->findOneBy(['code' => $data['statusCode']]);

            if (null === $status) {
                continue;
            }

            $booking = new Booking();
            $booking->setSpace($space);
            $booking->setUser($user);
            $booking->setStatus($status);
            $booking->setTitle($data['title']);
            $booking->setLastName($data['lastName']);
            $booking->setFirstName($data['firstName']);
            $booking->setOrganization($data['organization']);
            $booking->setRole($data['role']);
            $booking->setEmail($data['email']);
            $booking->setPhone($data['phone']);
            $booking->setDate(new \DateTime($data['dateModifier']));
            $booking->setStartTime(new \DateTime($data['startTime']));
            $booking->setEndTime(new \DateTime($data['endTime']));
            $booking->setParticipantCount($data['participantCount']);
            $booking->setAdditionalInformation($data['additionalInformation']);
            $booking->setRefusalReason($data['refusalReason'] ?? null);
            $booking->setCancellationReason($data['cancellationReason'] ?? null);

            $eventActivityType = $manager->getRepository(EventActivityType::class)
                ->findOneBy(['shortLabel' => $data['eventActivityTypeShortLabel']]);
            $booking->setEventActivityType($eventActivityType);

            if (null !== $data['informationSourceLabel']) {
                $informationSource = $manager->getRepository(InformationSource::class)
                    ->findOneBy(['label' => $data['informationSourceLabel']]);
                $booking->setInformationSource($informationSource);
            } else {
                $booking->setInformationSourceOther($data['informationSourceOther'] ?? null);
            }

            $submittedAt = new \DateTime($data['submittedAtModifier']);
            $this->forceSubmittedAt($booking, $submittedAt);

            if (null !== $data['processedAtModifier']) {
                $booking->setProcessedAt(new \DateTime($data['processedAtModifier']));
                $booking->setProcessingUser($users[$data['processedByEmail']] ?? null);
            }

            // Pièces jointes : ordre du jour + document ressource obligatoires.
            $this->addAttachment(
                $manager,
                $booking,
                BookingAttachment::TYPE_AGENDA,
                sprintf('ordre-du-jour-%d.pdf', $index + 1),
                ['Ordre du jour', $data['title'], 'Tiers-lieu DiverCity — Yaoundé']
            );

            $this->addAttachment(
                $manager,
                $booking,
                BookingAttachment::TYPE_RESOURCE_DOCUMENT,
                sprintf('document-ressource-%d.pdf', $index + 1),
                ['Document ressource', $data['title'], 'Tiers-lieu DiverCity — Yaoundé']
            );

            if ($data['hasOtherAttachment']) {
                $this->addAttachment(
                    $manager,
                    $booking,
                    BookingAttachment::TYPE_OTHER,
                    sprintf('note-complementaire-%d.pdf', $index + 1),
                    ['Note complémentaire', $data['title']]
                );
            }

            // Ressources produites par la rencontre (réservations traitées).
            for ($i = 0; $i < $data['resourceCount']; ++$i) {
                if (!isset($availableResources[$resourceCursor])) {
                    break;
                }

                $booking->addResource($availableResources[$resourceCursor]);
                ++$resourceCursor;
            }

            $manager->persist($booking);

            $this->addNotifications($manager, $booking, $data, $submittedAt, $users);

            $this->addReference(self::REFERENCE_PREFIX.($index + 1), $booking);
        }

        $manager->flush();
    }

    /**
     * @param array<string, User|null> $users
     * @param array<string, mixed>     $data
     */
    private function addNotifications(
        ObjectManager $manager,
        Booking $booking,
        array $data,
        \DateTimeInterface $submittedAt,
        array $users,
    ): void {
        $submission = new Notification();
        $submission->setBooking($booking);
        $submission->setUser($booking->getUser());
        $submission->setType(Notification::TYPE_SUBMISSION);
        $submission->setSentAt($submittedAt);
        $submission->setContent(sprintf(
            'Votre demande de réservation « %s » a bien été enregistrée et est en cours d\'examen.',
            $booking->getTitle()
        ));
        $manager->persist($submission);

        if (null === $data['processedAtModifier']) {
            return;
        }

        $processedAt = new \DateTime($data['processedAtModifier']);

        if ('ANNULEE' === $data['statusCode']) {
            $cancellation = new Notification();
            $cancellation->setBooking($booking);
            $cancellation->setUser($booking->getUser());
            $cancellation->setType(Notification::TYPE_CANCELLATION);
            $cancellation->setSentAt($processedAt);
            $cancellation->setContent(sprintf(
                'Votre réservation « %s » a été annulée.',
                $booking->getTitle()
            ));
            $manager->persist($cancellation);

            return;
        }

        $decision = new Notification();
        $decision->setBooking($booking);
        $decision->setUser($booking->getUser());
        $decision->setType(Notification::TYPE_DECISION);
        $decision->setSentAt($processedAt);
        $decision->setContent('ACCEPTEE' === $data['statusCode']
            ? sprintf('Votre réservation « %s » a été acceptée.', $booking->getTitle())
            : sprintf('Votre réservation « %s » a été refusée.', $booking->getTitle()));
        $manager->persist($decision);
    }

    /**
     * @param string[] $lines
     */
    private function addAttachment(
        ObjectManager $manager,
        Booking $booking,
        string $type,
        string $fileName,
        array $lines,
    ): void {
        $fileObject = $this->fileFactory->createGeneratedPdf($fileName, $lines);
        $manager->persist($fileObject);

        $attachment = new BookingAttachment();
        $attachment->setFileObject($fileObject);
        $attachment->setType($type);

        $booking->addBookingAttachment($attachment);
        $manager->persist($attachment);
    }

    /**
     * `submittedAt` est géré par Gedmo (Timestampable on create) et n'expose
     * pas de setter : on force la valeur par réflexion pour obtenir des dates
     * de soumission réalistes (et des KPI de délai de traitement cohérents).
     */
    private function forceSubmittedAt(Booking $booking, \DateTimeInterface $submittedAt): void
    {
        $property = new \ReflectionProperty(Booking::class, 'submittedAt');
        $property->setValue($booking, $submittedAt);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bookingsData(): array
    {
        return [
            [
                'title' => 'Atelier participatif sur la mobilité douce à Yaoundé',
                'userEmail' => 'lambda@test.com',
                'statusCode' => 'ACCEPTEE',
                'eventActivityTypeShortLabel' => 'Atelier participatif',
                'informationSourceLabel' => 'Réseaux sociaux',
                'lastName' => 'Doe',
                'firstName' => 'Jane',
                'organization' => 'Association Yaoundé Mobilité Durable',
                'role' => 'Chargée de projets',
                'email' => 'lambda@test.com',
                'phone' => '+237 699 12 34 56',
                'dateModifier' => '+7 days',
                'startTime' => '09:00',
                'endTime' => '13:00',
                'participantCount' => 35,
                'additionalInformation' => 'Prévoir une disposition en îlots pour les travaux de groupe et deux paperboards.',
                'submittedAtModifier' => '-21 days',
                'processedAtModifier' => '-19 days',
                'processedByEmail' => 'editor@test.com',
                'hasOtherAttachment' => true,
                'resourceCount' => 2,
            ],
            [
                'title' => 'Table ronde : financer la transition énergétique des villes secondaires',
                'userEmail' => 'editor@test.com',
                'statusCode' => 'ACCEPTEE',
                'eventActivityTypeShortLabel' => 'Conférence / Débat',
                'informationSourceLabel' => 'Réseau des acteurs de la plateforme Urbaine',
                'lastName' => 'Doe',
                'firstName' => 'John',
                'organization' => 'MINDHU — Direction de l\'Urbanisme',
                'role' => 'Sous-directeur',
                'email' => 'editor@test.com',
                'phone' => '+237 677 88 99 00',
                'dateModifier' => '+14 days',
                'startTime' => '14:00',
                'endTime' => '18:00',
                'participantCount' => 48,
                'additionalInformation' => 'Retransmission en direct prévue : accès à la fibre et à la régie son nécessaire.',
                'submittedAtModifier' => '-16 days',
                'processedAtModifier' => '-13 days',
                'processedByEmail' => 'admin@test.com',
                'hasOtherAttachment' => false,
                'resourceCount' => 1,
            ],
            [
                'title' => 'Formation SIG et données urbaines pour les communes',
                'userEmail' => 'lambda@test.com',
                'statusCode' => 'ACCEPTEE',
                'eventActivityTypeShortLabel' => 'Formation',
                'informationSourceLabel' => 'Recommandation',
                'lastName' => 'Ngo Bilong',
                'firstName' => 'Chantal',
                'organization' => 'Communauté Urbaine de Douala',
                'role' => 'Responsable SIG',
                'email' => 'c.ngobilong@example.cm',
                'phone' => '+237 655 44 33 22',
                'dateModifier' => '-21 days',
                'startTime' => '08:30',
                'endTime' => '16:30',
                'participantCount' => 22,
                'additionalInformation' => 'Session déjà tenue : 22 agents communaux formés sur QGIS et PostGIS.',
                'submittedAtModifier' => '-45 days',
                'processedAtModifier' => '-41 days',
                'processedByEmail' => 'editor@test.com',
                'hasOtherAttachment' => true,
                'resourceCount' => 3,
            ],
            [
                'title' => 'Séminaire national sur la planification urbaine inclusive',
                'userEmail' => 'lambda@test.com',
                'statusCode' => 'EN_ATTENTE',
                'eventActivityTypeShortLabel' => 'Séminaire / Colloque',
                'informationSourceLabel' => 'Bouche-à-oreille',
                'lastName' => 'Abena',
                'firstName' => 'Serge',
                'organization' => 'Université de Yaoundé I — Département de Géographie',
                'role' => 'Enseignant-chercheur',
                'email' => 's.abena@example.cm',
                'phone' => '+237 690 11 22 33',
                'dateModifier' => '+21 days',
                'startTime' => '09:00',
                'endTime' => '17:00',
                'participantCount' => 50,
                'additionalInformation' => 'Deux intervenants internationaux en visioconférence.',
                'submittedAtModifier' => '-5 days',
                'processedAtModifier' => null,
                'processedByEmail' => null,
                'hasOtherAttachment' => false,
                'resourceCount' => 0,
            ],
            [
                'title' => 'Hackathon « Données ouvertes et services urbains »',
                'userEmail' => 'lambda@test.com',
                'statusCode' => 'EN_ATTENTE',
                'eventActivityTypeShortLabel' => 'Hackathon',
                'informationSourceLabel' => null,
                'informationSourceOther' => 'Newsletter du projet PUC',
                'lastName' => 'Mbarga',
                'firstName' => 'Aline',
                'organization' => 'Collectif OpenData Cameroun',
                'role' => 'Coordinatrice',
                'email' => 'a.mbarga@example.cm',
                'phone' => '+237 698 76 54 32',
                'dateModifier' => '+28 days',
                'startTime' => '08:00',
                'endTime' => '20:00',
                'participantCount' => 40,
                'additionalInformation' => 'Besoin d\'un accès Wifi renforcé et de multiprises pour 20 postes.',
                'submittedAtModifier' => '-2 days',
                'processedAtModifier' => null,
                'processedByEmail' => null,
                'hasOtherAttachment' => true,
                'resourceCount' => 0,
            ],
            [
                'title' => 'Session de pré-incubation des projets urbains innovants',
                'userEmail' => 'editor@test.com',
                'statusCode' => 'EN_COURS_DE_TRAITEMENT',
                'eventActivityTypeShortLabel' => 'Incubation',
                'informationSourceLabel' => 'Réseau des acteurs de la plateforme Urbaine',
                'lastName' => 'Doe',
                'firstName' => 'John',
                'organization' => 'Incubateur CamTech',
                'role' => 'Responsable programme',
                'email' => 'editor@test.com',
                'phone' => '+237 677 00 11 22',
                'dateModifier' => '+9 days',
                'startTime' => '10:00',
                'endTime' => '15:00',
                'participantCount' => 18,
                'additionalInformation' => 'Demande en cours d\'instruction : pièces complémentaires attendues.',
                'submittedAtModifier' => '-4 days',
                'processedAtModifier' => null,
                'processedByEmail' => null,
                'hasOtherAttachment' => false,
                'resourceCount' => 0,
            ],
            [
                'title' => 'Soirée de réseautage des bureaux d\'études urbains',
                'userEmail' => 'lambda@test.com',
                'statusCode' => 'REFUSEE',
                'eventActivityTypeShortLabel' => 'Réseautage',
                'informationSourceLabel' => 'Réseaux sociaux',
                'lastName' => 'Fotso',
                'firstName' => 'Bertrand',
                'organization' => 'Cabinet Urbaplan Sarl',
                'role' => 'Directeur associé',
                'email' => 'b.fotso@example.cm',
                'phone' => '+237 694 33 22 11',
                'dateModifier' => '+2 days',
                'startTime' => '18:00',
                'endTime' => '22:00',
                'participantCount' => 60,
                'additionalInformation' => 'Cocktail dînatoire prévu sur place.',
                'submittedAtModifier' => '-12 days',
                'processedAtModifier' => '-10 days',
                'processedByEmail' => 'admin@test.com',
                'refusalReason' => 'Effectif annoncé (60) supérieur à la capacité maximale de l\'espace (50) et créneau au-delà des horaires d\'ouverture du tiers-lieu.',
                'hasOtherAttachment' => false,
                'resourceCount' => 0,
            ],
            [
                'title' => 'Atelier de restitution du diagnostic foncier communal',
                'userEmail' => 'lambda@test.com',
                'statusCode' => 'ANNULEE',
                'eventActivityTypeShortLabel' => 'Atelier participatif',
                'informationSourceLabel' => 'Recommandation',
                'lastName' => 'Owona',
                'firstName' => 'Marie',
                'organization' => 'Commune de Soa',
                'role' => 'Secrétaire générale',
                'email' => 'm.owona@example.cm',
                'phone' => '+237 676 55 44 33',
                'dateModifier' => '-8 days',
                'startTime' => '09:00',
                'endTime' => '12:30',
                'participantCount' => 25,
                'additionalInformation' => 'Restitution reportée à une date ultérieure.',
                'submittedAtModifier' => '-30 days',
                'processedAtModifier' => '-11 days',
                'processedByEmail' => 'editor@test.com',
                'cancellationReason' => 'Indisponibilité de l\'équipe municipale, report demandé par le demandeur.',
                'hasOtherAttachment' => false,
                'resourceCount' => 0,
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            SpaceFixtures::class,
            StatusFixtures::class,
            EventActivityTypeFixtures::class,
            InformationSourceFixtures::class,
        ];
    }
}
