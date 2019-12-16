<?php

declare(strict_types=1);

namespace Netgen\Layouts\Transfer\Input\Result;

use Ramsey\Uuid\UuidInterface;
use Throwable;

final class ErrorResult implements ResultInterface
{
    /**
     * @var string
     */
    private $entityType;

    /**
     * @var array<string, mixed>
     */
    private $data;

    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $entityId;

    /**
     * @var \Throwable
     */
    private $error;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(string $entityType, array $data, UuidInterface $entityId, Throwable $error)
    {
        $this->entityType = $entityType;
        $this->data = $data;
        $this->entityId = $entityId;
        $this->error = $error;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getEntityId(): UuidInterface
    {
        return $this->entityId;
    }

    /**
     * Returns the import error.
     */
    public function getError(): Throwable
    {
        return $this->error;
    }
}
