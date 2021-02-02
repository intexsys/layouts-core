<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\Tests\Controller\API\Config;

use Netgen\Bundle\LayoutsAdminBundle\Tests\Controller\API\JsonApiTestCase;
use Netgen\Layouts\Exception\RuntimeException;
use Netgen\Layouts\Tests\App\MockerContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function json_decode;
use const JSON_THROW_ON_ERROR;

final class LoadConfigTest extends JsonApiTestCase
{
    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\Controller\API\Config\LoadConfig::__construct
     * @covers \Netgen\Bundle\LayoutsAdminBundle\Controller\API\Config\LoadConfig::__invoke
     */
    public function testLoadConfig(): void
    {
        $clientContainer = $this->client->getContainer();
        if (!$clientContainer instanceof MockerContainer) {
            throw new RuntimeException('Symfony kernel is not configured yet.');
        }

        /** @var \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface $tokenManager */
        $tokenManager = $clientContainer->get('test.security.csrf.token_manager');

        /** @var string $tokenId */
        $tokenId = $clientContainer->getParameter('netgen_layouts.app.csrf_token_id');
        $currentToken = $tokenManager->getToken($tokenId);

        $this->client->request(Request::METHOD_GET, '/nglayouts/app/api/config');

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);

        $responseContent = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($responseContent);
        self::assertArrayHasKey('csrf_token', $responseContent);

        self::assertIsString($responseContent['csrf_token']);
        self::assertSame($currentToken->getValue(), $responseContent['csrf_token']);
    }
}
