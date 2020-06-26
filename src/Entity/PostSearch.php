<?php
namespace App\Entity;

class PostSearch
{
    /**
     * @var string|null
     */
    private $PostTitle;

    /**
     * @var int|null
     */
    private $status;

    /**
     * @return string|null
     */
    public function getPostTitle(): ?string
    {
        return $this->PostTitle;
    }

    /**
     * @param string|null $PostTitle
     */
    public function setPostTitle(?string $PostTitle): void
    {
        $this->PostTitle = $PostTitle;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int|null $status
     */
    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

}