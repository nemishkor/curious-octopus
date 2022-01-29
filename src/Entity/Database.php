<?php

namespace App\Entity;

use App\Repository\DatabaseRepository;
use App\Validator\PingDatabase;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: DatabaseRepository::class)]
#[ORM\Table(name: '`database`')]
#[UniqueEntity(['host', 'name'])]
#[PingDatabase]
class Database {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups('database')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups('database')]
    #[NotBlank]
    private ?string $host;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups('database')]
    #[NotBlank]
    private ?string $user;

    #[ORM\Column(type: 'string', length: 255)]
    #[NotBlank]
    private ?string $password;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups('database')]
    #[NotBlank]
    private ?string $name;

    public function getId(): ?int {
        return $this->id;
    }

    public function getHost(): ?string {
        return $this->host;
    }

    public function setHost(string $host): self {
        $this->host = $host;

        return $this;
    }

    public function getUser(): ?string {
        return $this->user;
    }

    public function setUser(string $user): self {
        $this->user = $user;

        return $this;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(string $password): self {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;

        return $this;
    }

}
