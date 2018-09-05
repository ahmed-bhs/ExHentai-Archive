<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExhentaiArchiverKeyRepository")
 */
class ExhentaiArchiverKey
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ExhentaiGallery", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $Gallery;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $Time;

    public function __construct(string $token)
    {
        $this->setTime(new \DateTime());
        $this->setToken($token);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGallery(): ?ExhentaiGallery
    {
        return $this->Gallery;
    }

    public function setGallery(ExhentaiGallery $Gallery): self
    {
        $this->Gallery = $Gallery;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->Time;
    }

    public function setTime(\DateTimeInterface $Time): self
    {
        $this->Time = $Time;

        return $this;
    }
}
