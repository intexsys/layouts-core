<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\Controller\Admin\Layouts;

use Netgen\Bundle\LayoutsBundle\Controller\AbstractController;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\HttpCache\ClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ClearLayoutCache extends AbstractController
{
    /**
     * @var \Netgen\Layouts\HttpCache\ClientInterface
     */
    private $httpCacheClient;

    public function __construct(ClientInterface $httpCacheClient)
    {
        $this->httpCacheClient = $httpCacheClient;
    }

    /**
     * Clears the HTTP cache for provided layout.
     */
    public function __invoke(Request $request, Layout $layout): Response
    {
        $this->denyAccessUnlessGranted('nglayouts:layout:clear_cache', $layout);

        if ($request->getMethod() !== Request::METHOD_POST) {
            return $this->render(
                '@NetgenLayoutsAdmin/admin/layouts/form/clear_layout_cache.html.twig',
                [
                    'submitted' => false,
                    'error' => false,
                    'layout' => $layout,
                ]
            );
        }

        $this->httpCacheClient->invalidateLayouts([$layout->getId()->toString()]);

        $cacheCleared = $this->httpCacheClient->commit();

        if ($cacheCleared) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        return $this->render(
            '@NetgenLayoutsAdmin/admin/layouts/form/clear_layout_cache.html.twig',
            [
                'submitted' => true,
                'error' => !$cacheCleared,
                'layout' => $layout,
            ],
            new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
