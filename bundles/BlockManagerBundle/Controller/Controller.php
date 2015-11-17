<?php

namespace Netgen\Bundle\BlockManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class Controller extends BaseController
{
    /**
     * @const string
     */
    const API_VERSION = 1;

    /**
     * Serializes the object.
     *
     * @param mixed $object
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function serializeObject($object)
    {
        $serializedObject = $this->get('serializer')->serialize(
            $object,
            'json',
            array('version' => self::API_VERSION)
        );

        $response = new JsonResponse();
        $response->setContent($serializedObject);

        return $response;
    }

    /**
     * Builds the view from the object.
     *
     * @param mixed $object
     * @param array $parameters
     * @param string $context
     *
     * @return \Netgen\BlockManager\View\ViewInterface
     */
    protected function buildViewObject($object, $parameters = array(), $context = 'view')
    {
        $viewBuilder = $this->get('netgen_block_manager.view.builder');

        return $viewBuilder->buildView($object, $parameters, $context);
    }

    /**
     * Renders the view.
     *
     * @param \Netgen\BlockManager\View\ViewInterface $view
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderViewObject($view)
    {
        $viewRenderer = $this->get('netgen_block_manager.view.renderer');
        $renderedView = $viewRenderer->renderView($view);

        $response = new Response();
        $response->setContent($renderedView);

        return $response;
    }

    /**
     * Returns the specified block definition from the registry
     *
     * @param string $definitionIdentifier
     *
     * @return \Netgen\BlockManager\BlockDefinition\BlockDefinitionInterface
     */
    protected function getBlockDefinition($definitionIdentifier)
    {
        $blockDefinitionRegistry = $this->get('netgen_block_manager.block_definition.registry');
        return $blockDefinitionRegistry->getBlockDefinition($definitionIdentifier);
    }
}
