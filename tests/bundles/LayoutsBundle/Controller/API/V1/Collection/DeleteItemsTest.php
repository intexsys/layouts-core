<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Tests\Controller\API\V1\Collection;

use Netgen\Bundle\LayoutsBundle\Tests\Controller\API\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteItemsTest extends JsonApiTestCase
{
    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Collection\DeleteItems::__construct
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Collection\DeleteItems::__invoke
     */
    public function testDeleteItems(): void
    {
        $this->client->request(
            Request::METHOD_DELETE,
            '/nglayouts/api/v1/collections/a79dde13-1f5c-51a6-bea9-b766236be49e/items',
            [],
            [],
            [],
            $this->jsonEncode([])
        );

        $this->assertEmptyResponse($this->client->getResponse());
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Collection\DeleteItems::__invoke
     */
    public function testDeleteItemsWithNonExistentCollection(): void
    {
        $this->client->request(
            Request::METHOD_DELETE,
            '/nglayouts/api/v1/collections/ffffffff-ffff-ffff-ffff-ffffffffffff/items',
            [],
            [],
            [],
            $this->jsonEncode([])
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_NOT_FOUND,
            'Could not find collection with identifier "ffffffff-ffff-ffff-ffff-ffffffffffff"'
        );
    }
}
