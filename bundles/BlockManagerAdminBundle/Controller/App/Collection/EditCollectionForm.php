<?php

declare(strict_types=1);

namespace Netgen\Bundle\BlockManagerAdminBundle\Controller\App\Collection;

use Netgen\BlockManager\API\Service\CollectionService;
use Netgen\BlockManager\API\Values\Collection\Collection;
use Netgen\BlockManager\Collection\Form\CollectionEditType;
use Netgen\BlockManager\View\ViewInterface;
use Netgen\Bundle\BlockManagerBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class EditCollectionForm extends AbstractController
{
    /**
     * @var \Netgen\BlockManager\API\Service\CollectionService
     */
    private $collectionService;

    public function __construct(CollectionService $collectionService)
    {
        $this->collectionService = $collectionService;
    }

    /**
     * Displays and processes collection draft edit form.
     *
     * @return \Netgen\BlockManager\View\ViewInterface|\Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Collection $collection, Request $request)
    {
        $updateStruct = $this->collectionService->newCollectionUpdateStruct($collection);

        $form = $this->createForm(
            CollectionEditType::class,
            $updateStruct,
            [
                'collection' => $collection,
                'action' => $this->generateUrl(
                    'ngbm_app_collection_collection_form_edit',
                    [
                        'collectionId' => $collection->getId(),
                    ]
                ),
            ]
        );

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $this->denyAccessUnlessGranted('nglayouts:api:read');

            return $this->buildView($form, ViewInterface::CONTEXT_API);
        }

        $this->denyAccessUnlessGranted('nglayouts:collection:edit');

        if ($form->isValid()) {
            $this->collectionService->updateCollection($collection, $form->getData());

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        return $this->buildView(
            $form,
            ViewInterface::CONTEXT_API,
            [],
            new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
