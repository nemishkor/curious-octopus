<?php

namespace App\Entity\User;

use App\Entity\User;
use App\Repository\User\ApiTokenRepository;
use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
class ApiToken {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups('apiToken')]
    private string $token;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $expires;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $created;

    public function __construct(User $user) {
        $this->user = $user;
        $this->token = bin2hex(random_bytes(20));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->created = new DateTime('now', new DateTimeZone('UTC'));
        $this->expires = clone $this->created;
        $this->expires->add(new DateInterval('P1D'));
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getToken(): ?string {
        return $this->token;
    }

    public function setToken(string $token): self {
        $this->token = $token;

        return $this;
    }

    public function getExpires(): ?DateTimeInterface {
        return $this->expires;
    }

    public function setExpires(DateTimeInterface $expires): self {
        $this->expires = $expires;

        return $this;
    }

    public function getCreated(): ?DateTimeInterface {
        return $this->created;
    }

}
