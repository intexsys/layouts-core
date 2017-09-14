<?php

namespace Netgen\BlockManager\API\Service;

interface Service
{
    /**
     * Runs the provided callable inside a transaction.
     *
     * @param callable $callable
     *
     * @return $mixed
     */
    public function transaction(callable $callable);

    /**
     * Begins a transaction.
     */
    public function beginTransaction();

    /**
     * Commits the transaction.
     *
     * @throws \Netgen\BlockManager\Exception\RuntimeException If no transaction has been started
     */
    public function commitTransaction();

    /**
     * Rollbacks the transaction.
     *
     * @throws \Netgen\BlockManager\Exception\RuntimeException If no transaction has been started
     */
    public function rollbackTransaction();
}
