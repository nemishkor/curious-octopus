<?php

namespace App\Entity;

use App\Enum\QueryState;
use App\Repository\QueryRepository;
use App\Validator\QueryString;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: QueryRepository::class)]
class Query {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups('query')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    #[Groups('query')]
    private DateTimeInterface $created;

    #[ORM\Column(type: 'text')]
    #[NotNull]
    #[QueryString]
    #[Groups('query')]
    private ?string $string;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups('query')]
    private string $state;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups('query')]
    private ?int $progressTotal = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups('query')]
    private ?int $progressCurrent = null;

    #[ORM\OneToMany(mappedBy: 'query', targetEntity: Job::class, orphanRemoval: true)]
    private $jobs;

    public function __construct() {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->created = new DateTime('now', new DateTimeZone('UTC'));
        $this->state = QueryState::IN_QUEUE;
        $this->jobs = new ArrayCollection();
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
        if ($this->progressCurrent >= $this->progressTotal) {
            $this->setState(QueryState::COMPILING);
        }

        return $this;
    }

    /**
     * @return Collection|Job[]
     */
    public function getJobs(): Collection {
        return $this->jobs;
    }

    public function addJob(Job $job): self {
        if (!$this->jobs->contains($job)) {
            $this->jobs[] = $job;
            $job->setQuery($this);
        }

        return $this;
    }

    public function removeJob(Job $job): self {
        if ($this->jobs->removeElement($job)) {
            // set the owning side to null (unless already changed)
            if ($job->getQuery() === $this) {
                $job->setQuery(null);
            }
        }

        return $this;
    }

}
