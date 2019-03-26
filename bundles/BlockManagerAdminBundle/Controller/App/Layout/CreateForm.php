<?php

declare(strict_types=1);

namespace Netgen\Bundle\BlockManagerAdminBundle\Controller\App\Layout;

use Netgen\BlockManager\API\Service\LayoutService;
use Netgen\BlockManager\API\Values\Layout\LayoutCreateStruct;
use Netgen\BlockManager\Exception\RuntimeException;
use Netgen\BlockManager\Layout\Form\CreateType;
use Netgen\BlockManager\Locale\LocaleProviderInterface;
use Netgen\BlockManager\View\ViewInterface;
use Netgen\Bundle\BlockManagerBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateForm extends Controller
{
    /**
     * @var \Netgen\BlockManager\API\Service\LayoutService
     */
    private $layoutService;

    /**
     * @var \Netgen\BlockManager\Locale\LocaleProviderInterface
     */
    private $localeProvider;

    public function __construct(LayoutService $layoutService, LocaleProviderInterface $localeProvider)
    {
        $this->layoutService = $layoutService;
        $this->localeProvider = $localeProvider;
    }

    /**
     * Displays and processes layout create form.
     *
     * @return \Netgen\BlockManager\View\ViewInterface|\Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request)
    {
        $this->denyAccessUnlessGranted('nglayouts:layout:add');

        $availableLocales = $this->localeProvider->getAvailableLocales();
        if (empty($availableLocales)) {
            throw new RuntimeException('There are no available locales configured in the system.');
        }

        $createStruct = new LayoutCreateStruct();
        $createStruct->mainLocale = (string) array_key_first($availableLocales);

        $form = $this->createForm(
            CreateType::class,
            $createStruct,
            [
                'action' => $this->generateUrl(
                    'ngbm_app_layout_form_create'
                ),
            ]
        );

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->buildView($form, ViewInterface::CONTEXT_API);
        }

        if ($form->isValid()) {
            $createdLayout = $this->layoutService->createLayout($createStruct);

            return new JsonResponse(
                [
                    'id' => $createdLayout->getId(),
                ],
                Response::HTTP_CREATED
            );
        }

        return $this->buildView(
            $form,
            ViewInterface::CONTEXT_API,
            [],
            new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
