<?php

/**
 * Copyright (c) Jakub Ryszka.
 */

namespace App\Entity;

use App\Entity\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Task.
 */
#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'tasks')]
#[ORM\HasLifecycleCallbacks]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\ManyToOne(targetEntity: Category::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Category $category = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Type(User::class)]
    private ?User $author = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Type('string')]
    private ?string $comment = null;

    #[ORM\Column(type: 'string', length: 32, enumType: TaskStatus::class)]
    private TaskStatus $status = TaskStatus::PENDING;

    #[ORM\Column(type: 'integer')]
    private int $votesCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $minVotesRequired = 0;

    /**
     * Set created at value.
     */
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Set updated at value.
     */
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for Title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for Title.
     *
     * @param string|null $title Title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Getter for CreatedAt.
     *
     * @return \DateTimeImmutable|null CreatedAt
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for CreatedAt.
     *
     * @param \DateTimeImmutable|null $createdAt CreatedAt
     */
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Getter for UpdatedAt.
     *
     * @return \DateTimeImmutable|null UpdatedAt
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter for UpdatedAt.
     *
     * @param \DateTimeImmutable|null $updatedAt UpdatedAt
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Getter for StartsAt.
     *
     * @return \DateTimeImmutable|null StartsAt
     */
    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    /**
     * Setter for StartsAt.
     *
     * @param \DateTimeImmutable|null $startsAt StartsAt
     */
    public function setStartsAt(?\DateTimeImmutable $startsAt): void
    {
        $this->startsAt = $startsAt;
    }

    /**
     * Getter for EndsAt.
     *
     * @return \DateTimeImmutable|null EndsAt
     */
    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    /**
     * Setter for EndsAt.
     *
     * @param \DateTimeImmutable|null $endsAt EndsAt
     */
    public function setEndsAt(?\DateTimeImmutable $endsAt): void
    {
        $this->endsAt = $endsAt;
    }

    /**
     * Getter for Category.
     *
     * @return Category|null Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Setter for Category.
     *
     * @param Category|null $category Category
     *
     * @return static Static
     */
    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Getter for Author.
     *
     * @return User|null Author
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for Author.
     *
     * @param User|null $author Author
     *
     * @return static Static
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Getter for Comment.
     *
     * @return string|null Comment
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Setter for Comment.
     *
     * @param string|null $comment Comment
     *
     * @return static Static
     */
    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Getter for Status.
     *
     * @return TaskStatus Status
     */
    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    /**
     * Setter for Status.
     *
     * @param TaskStatus $status Status
     */
    public function setStatus(TaskStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * Getter for VotesCount.
     *
     * @return int VotesCount
     */
    public function getVotesCount(): int
    {
        return $this->votesCount;
    }

    /**
     * Setter for VotesCount.
     *
     * @param int $votesCount VotesCount
     */
    public function setVotesCount(int $votesCount): void
    {
        $this->votesCount = $votesCount;
    }

    /**
     * Getter for MinVotesRequired.
     *
     * @return int MinVotesRequired
     */
    public function getMinVotesRequired(): int
    {
        return $this->minVotesRequired;
    }

    /**
     * Setter for MinVotesRequired.
     *
     * @param int $minVotesRequired MinVotesRequired
     */
    public function setMinVotesRequired(int $minVotesRequired): void
    {
        $this->minVotesRequired = $minVotesRequired;
    }

    /**
     * Check if task is started.
     *
     * @return bool True if started, false otherwise
     */
    public function isStarted(): bool
    {
        return null === $this->startsAt || $this->startsAt <= new \DateTimeImmutable();
    }

    /**
     * Check if task is expired.
     *
     * @return bool True if expired, false otherwise
     */
    public function isExpired(): bool
    {
        return null !== $this->endsAt && $this->endsAt < new \DateTimeImmutable();
    }

    /**
     * Check if task has met threshold.
     *
     * @return bool True if met, false otherwise
     */
    public function hasMetThreshold(): bool
    {
        return $this->votesCount >= $this->minVotesRequired;
    }
}
