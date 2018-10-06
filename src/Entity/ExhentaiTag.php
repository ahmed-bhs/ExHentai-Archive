<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExhentaiTagRepository")
 */
class ExhentaiTag
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ExhentaiTagNamespace", inversedBy="Tags")
     * @ORM\JoinColumn(nullable=true)
     */
    private $Namespace;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ExhentaiGallery", mappedBy="Tags")
     * @Serializer\Exclude()
     */
    private $Galleries;

    public function __construct()
    {
        $this->Galleries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getNamespace(): ?ExhentaiTagNamespace
    {
        return $this->Namespace;
    }

    public function setNamespace(?ExhentaiTagNamespace $Namespace): self
    {
        $this->Namespace = $Namespace;

        return $this;
    }

    /**
     * @return Collection|ExhentaiGallery[]
     */
    public function getGalleries(): Collection
    {
        return $this->Galleries;
    }

    public function addGallery(ExhentaiGallery $gallery): self
    {
        if (!$this->Galleries->contains($gallery)) {
            $this->Galleries[] = $gallery;
        }

        return $this;
    }

    public function removeGallery(ExhentaiGallery $gallery): self
    {
        if ($this->Galleries->contains($gallery)) {
            $this->Galleries->removeElement($gallery);
        }

        return $this;
    }
}
