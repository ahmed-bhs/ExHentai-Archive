<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DownloadStateRepository")
 */
class DownloadState
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ExhentaiGallery", inversedBy="downloadState", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $Gallery;

    /**
     * @ORM\Column(type="integer")
     */
    private $State;

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

    public function getState(): ?int
    {
        return $this->State;
    }

    public function setState(int $State): self
    {
        $this->State = $State;

        return $this;
    }
}
