<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExhentaiGalleryRepository")
 */
class ExhentaiGallery
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=12)
     */
    private $token;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $TitleJapan;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ExhentaiCategory", inversedBy="Galleries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Category;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $Posted;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Uploader;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $Filesize;

    /**
     * @ORM\Column(type="integer")
     */
    private $FileCount;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $Expunged;

    /**
     * @ORM\Column(type="float")
     */
    private $Rating;

    /**
     * @ORM\Column(type="integer")
     */
    private $TorrentCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $downloadState;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ExhentaiTag", inversedBy="Galleries")
     */
    private $Tags;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ExhentaiArchiverKey", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $ArchiverKey;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExhentaiGalleryImage", mappedBy="Gallery", orphanRemoval=true)
     */
    private $Images;

    public function __construct()
    {
        $this->Tags = new ArrayCollection();
        $this->Images = new ArrayCollection();
    }

    /**
     * @deprecated USE REPOSITORY
     *
     * @param \stdClass $json
     * @return ExhentaiGallery
     */
    public static function fromApi(\stdClass $json)
    {
        $self = new self();
        $self->setId($json->gid)
            ->setToken($json->token)
            ->setArchiverKey( new ExhentaiArchiverKey($json->archiver_key))
            ->setTitle($json->title)
            ->setTitleJapan($json->title_jpn)
            ->setCategory((new ExhentaiCategory())->setTitle($json->category))
            ->setUploader($json->uploader)
            ->setPosted(new \DateTime('@'.$json->posted))
            ->setFileCount($json->filecount)
            ->setFilesize($json->filesize)
            ->setExpunged($json->expunged)
            ->setRating($json->rating);
        $namespaces = [];
        foreach($json->tags as $tagString) {
            $tag = new ExhentaiTag();

            if(strpos($tagString, ':') !== FALSE) {
                list($namespace, $tagString) = explode(':', $tagString);
                if (array_key_exists($namespace, $namespaces)) {
                    $namespace = $namespaces[$namespace];
                } else {
                    $namespace = (new ExhentaiTagNamespace())->setName($namespace);
                }
                $tag->setNamespace($namespace);
            }

            $self->addTag($tag->setName($tagString));
        }

        return $self;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitleJapan(): ?string
    {
        return $this->TitleJapan;
    }

    public function setTitleJapan(string $TitleJapan): self
    {
        $this->TitleJapan = $TitleJapan;

        return $this;
    }

    public function getCategory(): ?ExhentaiCategory
    {
        return $this->Category;
    }

    public function setCategory(?ExhentaiCategory $Category): self
    {
        $this->Category = $Category;

        return $this;
    }

    public function getPosted(): ?\DateTimeInterface
    {
        return $this->Posted;
    }

    public function setPosted(\DateTimeInterface $Posted): self
    {
        $this->Posted = $Posted;

        return $this;
    }

    public function getUploader(): ?string
    {
        return $this->Uploader;
    }

    public function setUploader(?string $Uploader): self
    {
        $this->Uploader = $Uploader;

        return $this;
    }

    public function getFilesize(): ?float
    {
        return $this->Filesize;
    }

    public function setFilesize(?float $Filesize): self
    {
        $this->Filesize = $Filesize;

        return $this;
    }

    public function getFileCount(): ?int
    {
        return $this->FileCount;
    }

    public function setFileCount(int $FileCount): self
    {
        $this->FileCount = $FileCount;

        return $this;
    }

    public function getExpunged(): ?bool
    {
        return $this->Expunged;
    }

    public function setExpunged(?bool $Expunged): self
    {
        $this->Expunged = $Expunged;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->Rating;
    }

    public function setRating(float $Rating): self
    {
        $this->Rating = $Rating;

        return $this;
    }

    public function getTorrentCount(): ?int
    {
        return $this->TorrentCount;
    }

    public function setTorrentCount(int $TorrentCount): self
    {
        $this->TorrentCount = $TorrentCount;

        return $this;
    }

    public function getDownloadState(): ?int
    {
        return $this->downloadState;
    }

    public function setDownloadState(int $downloadState): self
    {
        $this->downloadState = $downloadState;

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
            $tag->addGallery($this);
        }

        return $this;
    }

    public function removeTag(ExhentaiTag $tag): self
    {
        if ($this->Tags->contains($tag)) {
            $this->Tags->removeElement($tag);
            $tag->removeGallery($this);
        }

        return $this;
    }

    public function getArchiverKey(): ?ExhentaiArchiverKey
    {
        return $this->ArchiverKey;
    }

    public function setArchiverKey(ExhentaiArchiverKey $ArchiverKey): self
    {
        $this->ArchiverKey = $ArchiverKey;

        $this->ArchiverKey->setGallery($this);

        return $this;
    }

    /**
     * @return Collection|ExhentaiGalleryImage[]
     */
    public function getImages(): Collection
    {
        return $this->Images;
    }

    public function addImage(ExhentaiGalleryImage $image): self
    {
        if (!$this->Images->contains($image)) {
            $this->Images[] = $image;
            $image->setGallery($this);
        }

        return $this;
    }

    public function removeImage(ExhentaiGalleryImage $image): self
    {
        if ($this->Images->contains($image)) {
            $this->Images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getGallery() === $this) {
                $image->setGallery(null);
            }
        }

        return $this;
    }
}
