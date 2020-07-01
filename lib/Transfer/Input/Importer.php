<?php

declare(strict_types=1);

namespace Netgen\Layouts\Transfer\Input;

use Netgen\Layouts\API\Service\TransactionService;
use Netgen\Layouts\API\Values\Value;
use Netgen\Layouts\Exception\Transfer\TransferException;
use Netgen\Layouts\Transfer\EntityHandlerInterface;
use Netgen\Layouts\Transfer\Input\Result\ErrorResult;
use Netgen\Layouts\Transfer\Input\Result\SkippedResult;
use Netgen\Layouts\Transfer\Input\Result\SuccessResult;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;
use Traversable;
use function file_get_contents;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Importer creates Netgen Layouts entities from the serialized JSON data.
 */
final class Importer implements ImporterInterface
{
    /**
     * The path to the root schema directory.
     */
    private const SCHEMA_FILE = __DIR__ . '/../../../resources/schemas/import.json';

    /**
     * @var \Netgen\Layouts\API\Service\TransactionService
     */
    private $transactionService;

    /**
     * @var \Netgen\Layouts\Transfer\Input\JsonValidatorInterface
     */
    private $jsonValidator;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $entityHandlers;

    public function __construct(
        TransactionService $transactionService,
        JsonValidatorInterface $jsonValidator,
        ContainerInterface $entityHandlers
    ) {
        $this->transactionService = $transactionService;
        $this->jsonValidator = $jsonValidator;
        $this->entityHandlers = $entityHandlers;
    }

    public function importData(string $data, ImportOptions $options): Traversable
    {
        $schema = (string) file_get_contents(self::SCHEMA_FILE);
        $this->jsonValidator->validateJson($data, $schema);

        $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        foreach ($data['entities'] as $entityData) {
            $uuid = Uuid::fromString($entityData['id']);

            $handler = $this->getEntityHandler($entityData['__type']);
            $entityExists = $uuid instanceof UuidInterface && $handler->entityExists($uuid);

            if ($entityExists && $options->skipExisting()) {
                yield new SkippedResult($entityData['__type'], $entityData, $uuid);

                continue;
            }

            try {
                $entity = $this->transactionService->transaction(
                    static function () use ($handler, $entityData, $options, $uuid, $entityExists): Value {
                        $keepUuid = true;

                        if ($entityExists) {
                            if ($options->overwriteExisting()) {
                                $handler->deleteEntity($uuid);
                            } elseif ($options->copyExisting()) {
                                $keepUuid = false;
                            }
                        }

                        return $handler->importEntity($entityData, $keepUuid);
                    }
                );

                yield new SuccessResult($entityData['__type'], $entityData, $entity->getId(), $entity);
            } catch (Throwable $t) {
                yield new ErrorResult($entityData['__type'], $entityData, $uuid, $t);
            }
        }
    }

    /**
     * Returns the entity handler for provided entity type from the collection.
     *
     * @throws \Netgen\Layouts\Exception\Transfer\TransferException If the entity handler does not exist or is not of correct type
     */
    private function getEntityHandler(string $type): EntityHandlerInterface
    {
        if (!$this->entityHandlers->has($type)) {
            throw TransferException::noEntityHandler($type);
        }

        $entityHandler = $this->entityHandlers->get($type);
        if (!$entityHandler instanceof EntityHandlerInterface) {
            throw TransferException::noEntityHandler($type);
        }

        return $entityHandler;
    }
}
