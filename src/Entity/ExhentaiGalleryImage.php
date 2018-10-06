<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExhentaiGalleryImageRepository")
 */
class ExhentaiGalleryImage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ExhentaiGallery", inversedBy="Images")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Gallery;

    /**
     * @ORM\Column(type="integer")
     */
    private $Type;

    /**
     * @ORM\Column(type="integer")
     */
    private $Page;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGallery(): ?ExhentaiGallery
    {
        return $this->Gallery;
    }

    public function setGallery(?ExhentaiGallery $Gallery): self
    {
        $this->Gallery = $Gallery;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->Type;
    }

    public function setType(int $Type): self
    {
        $this->Type = $Type;

        return $this;
    }

    public function getPage(): ?int
    {
        return $this->Page;
    }

    public function setPage(int $Page): self
    {
        $this->Page = $Page;

        return $this;
    }
}
