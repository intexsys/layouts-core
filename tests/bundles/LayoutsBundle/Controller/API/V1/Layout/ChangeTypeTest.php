<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Tests\Controller\API\V1\Layout;

use Netgen\Bundle\LayoutsBundle\Tests\Controller\API\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ChangeTypeTest extends JsonApiTestCase
{
    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Layout\ChangeType::__construct
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Layout\ChangeType::__invoke
     */
    public function testChangeType(): void
    {
        $data = $this->jsonEncode(
            [
                'new_type' => '4_zones_b',
                'zone_mappings' => [
                    'left' => ['left'],
                    'right' => ['right'],
                ],
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/layouts/81168ed3-86f9-55ea-b153-101f96f2c136/change_type?html=false',
            [],
            [],
            [],
            $data
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'v1/layouts/change_type',
            Response::HTTP_OK
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Controller\API\V1\Layout\ChangeType::__invoke
     */
    public function testChangeTypeWithoutMappings(): void
    {
        $data = $this->jsonEncode(
            [
                'new_type' => '4_zones_b',
            ]
        );

        $this->client->request(
            Request::METHOD_POST,
            '/nglayouts/api/v1/layouts/81168ed3-86f9-55ea-b153-101f96f2c136/change_type?html=false',
            [],
            [],
            [],
            $data
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'v1/layouts/change_type_without_mappings',
            Response::HTTP_OK
        );
    }
}
