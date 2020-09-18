<?php

namespace App\Entity;

use App\Repository\PostDateTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostDateTypeRepository::class)
 */
class PostDateType
{
    public const Date_start_collect_fund = 1;
    public const Date_end_collect_fund = 2;
    public const Date_transfer_fund_to_author = 3;
    public const Date_author_received_fund = 4;
    public const Date_update_info_project_in_progress = 5;
    public const Date_close_project = 6;


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $dateType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateType(): ?string
    {
        return $this->dateType;
    }

    public function setDateType(?string $dateType): self
    {
        $this->dateType = $dateType;

        return $this;
    }
}
