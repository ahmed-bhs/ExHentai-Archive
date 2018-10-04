<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExhentaiTagNamespaceRepository")
 */
class ExhentaiTagNamespace
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExhentaiTag", mappedBy="Namespace")
     * @Serializer\Exclude()
     */
    private $Tags;

    public function __construct()
    {
        $this->Tags = new ArrayCollection();
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

    /**
     * @return Collection|ExhentaiTag[]
     */
    public function getTags(): Collection
    {
        return $this->Tags;
    }

    public function addTag(ExhentaiTag $tag): self
    {
        if (!$this->Tags->contains($tag)) {
            $this->Tags[] = $tag;
            $tag->setNamespace($this);
        }

        return $this;
    }

    public function removeTag(ExhentaiTag $tag): self
    {
        if ($this->Tags->contains($tag)) {
            $this->Tags->removeElement($tag);
            // set the owning side to null (unless already changed)
            if ($tag->getNamespace() === $this) {
                $tag->setNamespace(null);
            }
        }

        return $this;
    }
}
