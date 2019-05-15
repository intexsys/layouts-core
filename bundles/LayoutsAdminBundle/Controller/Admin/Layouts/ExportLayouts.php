<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\Controller\Admin\Layouts;

use Netgen\BlockManager\Transfer\Output\SerializerInterface;
use Netgen\Bundle\BlockManagerBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class ExportLayouts extends AbstractController
{
    /**
     * @var \Netgen\BlockManager\Transfer\Output\SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Exports the provided list of layouts.
     */
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('nglayouts:ui:access');

        $serializedLayouts = $this->serializer->serializeLayouts(
            array_unique($request->request->get('layout_ids'))
        );

        $json = json_encode($serializedLayouts, JSON_PRETTY_PRINT);

        $response = new Response($json);

        $fileName = sprintf('layouts_export_%s.json', date('Y-m-d_H-i-s'));
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', $disposition);
        // X-Filename header is needed for AJAX file download support
        $response->headers->set('X-Filename', $fileName);

        return $response;
    }
}
