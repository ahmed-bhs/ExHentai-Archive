<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExhentaiCategoryRepository")
 */
class ExhentaiCategory
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
    private $Title;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExhentaiGallery", mappedBy="Category")
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

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;

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
            $gallery->setCategory($this);
        }

        return $this;
    }

    public function removeGallery(ExhentaiGallery $gallery): self
    {
        if ($this->Galleries->contains($gallery)) {
            $this->Galleries->removeElement($gallery);
            // set the owning side to null (unless already changed)
            if ($gallery->getCategory() === $this) {
                $gallery->setCategory(null);
            }
        }

        return $this;
    }
}
