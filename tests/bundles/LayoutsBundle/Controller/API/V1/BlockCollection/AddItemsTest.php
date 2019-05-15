<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Tests\Controller\API\V1\BlockCollection;

use Netgen\Bundle\LayoutsBundle\Tests\Controller\API\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;

final class AddItemsTest extends JsonApiTestCase
{
    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__construct
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItems(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [
                    [
                        'value' => 73,
                        'value_type' => 'my_value_type',
                        'position' => 3,
                    ],
                    [
                        'value' => 74,
                        'value_type' => 'my_value_type',
                    ],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/default/items',
            [],
            [],
            [],
            $data
        );

        $this->assertEmptyResponse($this->client->getResponse());
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithNonExistentBlock(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [
                    [
                        'value' => 73,
                        'value_type' => 'my_value_type',
                        'position' => 3,
                    ],
                    [
                        'value' => 74,
                        'value_type' => 'my_value_type',
                    ],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/ffffffff-ffff-ffff-ffff-ffffffffffff/collections/default/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_NOT_FOUND,
            'Could not find block with identifier "ffffffff-ffff-ffff-ffff-ffffffffffff"'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithNonExistentCollection(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [
                    [
                        'value' => 73,
                        'value_type' => 'my_value_type',
                        'position' => 3,
                    ],
                    [
                        'value' => 74,
                        'value_type' => 'my_value_type',
                    ],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/unknown/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'Collection with "unknown" identifier does not exist in the block.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithEmptyItems(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/default/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'There was an error validating "items": This value should not be blank.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithInvalidItems(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => 42,
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/default/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            Kernel::VERSION_ID >= 40200 ?
                'There was an error validating "items": This value should be of type array.' :
                'There was an error validating "items": Expected argument of type "array or Traversable", "integer" given'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithMissingItems(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/default/items',
            [],
            [],
            [],
            $this->jsonEncode([])
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'There was an error validating "items": This value should not be blank.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithInvalidValue(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [
                    [
                        'value' => [42],
                        'value_type' => 'my_value_type',
                        'position' => 3,
                    ],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/default/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'There was an error validating "[0][value]": This value should be of type scalar.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithMissingValue(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [
                    [
                        'value_type' => 'my_value_type',
                        'position' => 3,
                    ],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/default/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'There was an error validating "[0][value]": This field is missing.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithInvalidValueType(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [
                    [
                        'value' => 73,
                        'value_type' => 42,
                        'position' => 3,
                    ],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/default/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'There was an error validating "[0][value_type]": This value should be of type string.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithMissingValueType(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [
                    [
                        'value' => 73,
                        'position' => 3,
                    ],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/default/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'There was an error validating "[0][value_type]": This field is missing.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithInvalidPosition(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [
                    [
                        'value' => 73,
                        'value_type' => 'my_value_type',
                        'position' => '3',
                    ],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/default/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'There was an error validating "[0][position]": This value should be of type int.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithMissingPosition(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [
                    [
                        'value' => 73,
                        'value_type' => 'my_value_type',
                    ],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/featured/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'There was an error validating "position": This value should not be blank.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::__invoke
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\BlockCollection\AddItems::validateAddItems
     */
    public function testAddItemsWithOutOfRangePosition(): void
    {
        $data = $this->jsonEncode(
            [
                'items' => [
                    [
                        'value' => 73,
                        'value_type' => 'my_value_type',
                        'position' => 9999,
                    ],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/28df256a-2467-5527-b398-9269ccc652de/collections/default/items',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Argument "position" has an invalid state. Position is out of range.'
        );
    }
}
