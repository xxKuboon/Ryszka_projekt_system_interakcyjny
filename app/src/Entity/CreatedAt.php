<?php

/**
 * Copyright (c) Jakub Ryszka.
 */

namespace App\Entity;

use App\Repository\CreatedAtRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CreatedAt.
 */
#[ORM\Entity(repositoryClass: CreatedAtRepository::class)]
class CreatedAt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
