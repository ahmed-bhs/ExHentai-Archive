<?php

namespace App\Model;

class GalleryToken
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    public function __construct(int $id, string $token)
    {
        $this->id    = $id;
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
