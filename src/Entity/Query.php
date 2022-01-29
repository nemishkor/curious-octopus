<?php

namespace App\Entity;

use App\Enum\QueryState;
use App\Repository\QueryRepository;
use App\Validator\QueryString;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: QueryRepository::class)]
class Query {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $created;

    #[ORM\Column(type: 'text')]
    #[NotNull]
    #[QueryString]
    private ?string $string;

    #[ORM\Column(type: 'string', length: 255)]
    private string $state;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $progressTotal = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $progressCurrent = null;

    public function __construct() {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->created = new DateTime('now', new DateTimeZone('UTC'));
        $this->state = QueryState::IN_QUEUE;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getCreated(): ?DateTimeInterface {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): self {
        $this->created = $created;

        return $this;
    }

    public function getString(): ?string {
        return $this->string;
    }

    public function setString(string $string): self {
        $this->string = $string;

        return $this;
    }

    public function getState(): ?string {
        return $this->state;
    }

    public function setState(string $state): self {
        $this->state = $state;

        return $this;
    }

    public function getProgressTotal(): ?int {
        return $this->progressTotal;
    }

    public function setProgressTotal(int $progressTotal): self {
        $this->progressTotal = $progressTotal;

        return $this;
    }

    public function getProgressCurrent(): ?int {
        return $this->progressCurrent;
    }

    public function setProgressCurrent(?int $progressCurrent): self {
        $this->progressCurrent = $progressCurrent;

        return $this;
    }

}
