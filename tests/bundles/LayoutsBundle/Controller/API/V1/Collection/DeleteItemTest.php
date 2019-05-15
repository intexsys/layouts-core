<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Tests\Controller\API\V1\Collection;

use Netgen\Bundle\LayoutsBundle\Tests\Controller\API\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteItemTest extends JsonApiTestCase
{
    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Collection\DeleteItem::__construct
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Collection\DeleteItem::__invoke
     */
    public function testDeleteItem(): void
    {
        $data = $this->jsonEncode([]);

        $this->client->request(
            Request::METHOD_DELETE,
            '/bm/api/v1/collections/items/7',
            [],
            [],
            [],
            $data
        );

        $this->assertEmptyResponse($this->client->getResponse());
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Collection\DeleteItem::__invoke
     */
    public function testDeleteItemWithNonExistentItem(): void
    {
        $data = $this->jsonEncode([]);

        $this->client->request(
            Request::METHOD_DELETE,
            '/bm/api/v1/collections/items/9999',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_NOT_FOUND,
            'Could not find item with identifier "9999"'
        );
    }
}
