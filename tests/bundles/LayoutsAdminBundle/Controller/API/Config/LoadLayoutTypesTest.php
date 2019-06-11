<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\Tests\Controller\API\Config;

use Netgen\Bundle\LayoutsAdminBundle\Tests\Controller\API\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LoadLayoutTypesTest extends JsonApiTestCase
{
    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\Controller\API\Config\LoadLayoutTypes::__construct
     * @covers \Netgen\Bundle\LayoutsAdminBundle\Controller\API\Config\LoadLayoutTypes::__invoke
     */
    public function testLoadLayoutTypes(): void
    {
        $this->client->request(Request::METHOD_GET, '/nglayouts/api/v1/config/layout_types');

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);

        $responseContent = json_decode($response->getContent(), true);

        self::assertIsArray($responseContent);
        self::assertNotEmpty($responseContent);
    }
}
