<?php

/**
 * Task status enum.
 */

namespace App\Entity\Enum;

/**
 * Enum TaskStatus.
 */
enum TaskStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * Get label for translation.
     *
     * @return string Label
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'label.status_pending',
            self::APPROVED => 'label.status_approved',
            self::REJECTED => 'label.status_rejected',
        };
    }
}
