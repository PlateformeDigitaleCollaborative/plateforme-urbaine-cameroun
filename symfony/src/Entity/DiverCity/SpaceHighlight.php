<?php

namespace App\Entity\DiverCity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\File\FileObject;
use App\Repository\DiverCity\SpaceHighlightRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Temps fort annuel d'un espace : rapport + statistiques manuelles saisies
 * par l'admin pour une année donnée, affichés sur la page d'accueil.
 *
 * Une ligne par (espace, année) — permet de conserver l'historique des
 * années précédentes sans écraser les données au fil du temps.
 */
#[ORM\Entity(repositoryClass: SpaceHighlightRepository::class)]
#[ORM\Table(name: 'space_highlight', schema: 'divercity')]
#[ORM\UniqueConstraint(name: 'uniq_space_highlight_space_year_semester', columns: ['space_id', 'year', 'semester'])]
#[ApiResource(
    normalizationContext: ['groups' => [self::GROUP_READ]],
    denormalizationContext: ['groups' => [self::GROUP_WRITE]],
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: 'is_granted("ROLE_ADMIN")'),
        new Patch(security: 'is_granted("ROLE_ADMIN")'),
        new Delete(security: 'is_granted("ROLE_ADMIN")'),
    ],
)]
class SpaceHighlight
{
    public const GROUP_READ = 'divercity_space_highlight:read';
    public const GROUP_WRITE = 'divercity_space_highlight:write';

    public function __construct()
    {
        $this->statistics = new ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::GROUP_READ, Space::GROUP_READ])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Space::class, inversedBy: 'highlights')]
    #[ORM\JoinColumn(name: 'space_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_WRITE])]
    private ?Space $space = null;

    #[ORM\Column]
    #[Assert\Range(min: 2000, max: 2100)]
    #[Groups([self::GROUP_READ, self::GROUP_WRITE, Space::GROUP_READ])]
    private ?int $year = null;

    /**
     * 1 = premier semestre (janvier-juin), 2 = second semestre (juillet-décembre).
     */
    #[ORM\Column]
    #[Assert\Choice(choices: [1, 2])]
    #[Groups([self::GROUP_READ, self::GROUP_WRITE, Space::GROUP_READ])]
    private ?int $semester = null;

    #[ORM\ManyToOne(targetEntity: FileObject::class)]
    #[ORM\JoinColumn(name: 'report_file_object_id', referencedColumnName: 'id')]
    #[Groups([self::GROUP_READ, self::GROUP_WRITE, Space::GROUP_READ])]
    private ?FileObject $report = null;

    /**
     * @var Collection<int, SpaceStatistic>
     */
    #[ORM\OneToMany(targetEntity: SpaceStatistic::class, mappedBy: 'spaceHighlight', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups([self::GROUP_READ, self::GROUP_WRITE, Space::GROUP_READ])]
    private Collection $statistics;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups([self::GROUP_READ, Space::GROUP_READ])]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups([self::GROUP_READ])]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpace(): ?Space
    {
        return $this->space;
    }

    public function setSpace(?Space $space): static
    {
        $this->space = $space;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getSemester(): ?int
    {
        return $this->semester;
    }

    public function setSemester(int $semester): static
    {
        $this->semester = $semester;

        return $this;
    }

    public function getReport(): ?FileObject
    {
        return $this->report;
    }

    public function setReport(?FileObject $report): static
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return Collection<int, SpaceStatistic>
     */
    public function getStatistics(): Collection
    {
        return $this->statistics;
    }

    public function addStatistic(SpaceStatistic $statistic): static
    {
        if (!$this->statistics->contains($statistic)) {
            $this->statistics->add($statistic);
            $statistic->setSpaceHighlight($this);
        }

        return $this;
    }

    public function removeStatistic(SpaceStatistic $statistic): static
    {
        if ($this->statistics->removeElement($statistic)) {
            if ($statistic->getSpaceHighlight() === $this) {
                $statistic->setSpaceHighlight(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
}
