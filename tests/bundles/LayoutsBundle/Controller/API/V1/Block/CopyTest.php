<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Tests\Controller\API\V1\Block;

use Netgen\Bundle\LayoutsBundle\Tests\Controller\API\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CopyTest extends JsonApiTestCase
{
    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Block\Copy::__construct
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Block\Copy::__invoke
     */
    public function testCopy(): void
    {
        $data = $this->jsonEncode(
            [
                'parent_block_id' => 33,
                'parent_placeholder' => 'left',
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/34/copy?html=false',
            [],
            [],
            [],
            $data
        );

        self::assertResponse(
            $this->client->getResponse(),
            'v1/blocks/copy_block',
            Response::HTTP_CREATED
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Block\Copy::__invoke
     */
    public function testCopyWithNonExistentBlock(): void
    {
        $data = $this->jsonEncode([]);

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/9999/copy',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_NOT_FOUND,
            'Could not find block with identifier "9999"'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Block\Copy::__invoke
     */
    public function testCopyWithNonExistentTargetBlock(): void
    {
        $data = $this->jsonEncode(
            [
                'parent_block_id' => 9999,
                'parent_placeholder' => 'main',
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/34/copy',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_NOT_FOUND,
            'Could not find block with identifier "9999"'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Block\Copy::__invoke
     */
    public function testCopyWithNonExistentPlaceholder(): void
    {
        $data = $this->jsonEncode(
            [
                'parent_block_id' => 33,
                'parent_placeholder' => 'unknown',
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/34/copy',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Argument "placeholder" has an invalid state. Target block does not have the specified placeholder.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Block\Copy::__invoke
     */
    public function testCopyWithNonContainerTargetBlock(): void
    {
        $data = $this->jsonEncode(
            [
                'parent_block_id' => 37,
                'parent_placeholder' => 'main',
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/34/copy',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Argument "targetBlock" has an invalid state. Target block is not a container.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Block\Copy::__invoke
     */
    public function testCopyWithContainerInsideContainer(): void
    {
        $data = $this->jsonEncode(
            [
                'parent_block_id' => 38,
                'parent_placeholder' => 'main',
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/33/copy',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Argument "block" has an invalid state. Containers cannot be placed inside containers.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Block\Copy::__invoke
     */
    public function testCopyWithInvalidBlockId(): void
    {
        $data = $this->jsonEncode(
            [
                'parent_block_id' => [42],
                'parent_placeholder' => 'main',
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/34/copy',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'There was an error validating "blockId": This value should be of type scalar.'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Block\Copy::__invoke
     */
    public function testCopyWithMissingBlockId(): void
    {
        $data = $this->jsonEncode(
            [
                'parent_placeholder' => 'main',
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/en/blocks/34/copy',
            [],
            [],
            [],
            $data
        );

        $this->assertException(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            'There was an error validating "blockId": This value should not be blank.'
        );
    }
}
