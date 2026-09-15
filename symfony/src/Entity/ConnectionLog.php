<?php

namespace App\Entity;

use App\Entity\User\User;
use App\Repository\ConnectionLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Journal des connexions réussies des utilisateurs (alimenté par AuthenticationSuccessHandler).
 * Sert de base aux KPIs "connexions" et "utilisateurs actifs" de la PDC.
 * Pas d'ApiResource : ces données ne sont exposées qu'au travers des endpoints de statistiques agrégées.
 */
#[ORM\Entity(repositoryClass: ConnectionLogRepository::class)]
#[ORM\Index(columns: ['connected_at'], name: 'idx_connection_log_connected_at')]
#[ORM\Index(columns: ['country_code'], name: 'idx_connection_log_country_code')]
class ConnectionLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $connectedAt = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    /**
     * Code pays ISO 3166-1 alpha-2 (ex: "CM", "FR"), résolu depuis l'IP au moment de la
     * connexion par IpGeolocationService. Null si l'IP est privée, inconnue ou si le
     * service de géolocalisation n'a pas répondu.
     */
    #[ORM\Column(length: 2, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $countryName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getConnectedAt(): ?\DateTimeImmutable
    {
        return $this->connectedAt;
    }

    public function setConnectedAt(\DateTimeImmutable $connectedAt): static
    {
        $this->connectedAt = $connectedAt;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): static
    {
        $this->countryCode = $countryCode ? strtoupper($countryCode) : null;

        return $this;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function setCountryName(?string $countryName): static
    {
        $this->countryName = $countryName;

        return $this;
    }
}
