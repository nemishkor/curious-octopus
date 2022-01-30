<?php

namespace App\Entity;

use App\Enum\JobState;
use App\Repository\JobRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobRepository::class)]
class Job {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Query::class, inversedBy: 'jobs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Query $query;

    #[ORM\ManyToOne(targetEntity: Database::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Database $db;

    #[ORM\Column(type: 'string', length: 255)]
    private string $state;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $result;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $error;

    public function __construct(Query $query, Database $database) {
        $this->query = $query;
        $this->db = $database;
        $this->state = JobState::IN_QUEUE;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getQuery(): ?Query {
        return $this->query;
    }

    public function setQuery(?Query $query): self {
        $this->query = $query;

        return $this;
    }

    public function getDb(): ?Database {
        return $this->db;
    }

    public function setDb(?Database $db): self {
        $this->db = $db;

        return $this;
    }

    public function getState(): ?string {
        return $this->state;
    }

    public function setState(string $state): self {
        $this->state = $state;

        return $this;
    }

    public function getResult(): ?string {
        return $this->result;
    }

    public function setResult(?string $result): self {
        $this->result = $result;

        return $this;
    }

    public function getError(): ?string {
        return $this->error;
    }

    public function setError(?string $error): self {
        $this->error = $error;

        return $this;
    }

}
