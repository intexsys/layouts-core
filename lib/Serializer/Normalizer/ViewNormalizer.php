<?php

namespace Netgen\BlockManager\Serializer\Normalizer;

use Netgen\BlockManager\Serializer\SerializerAwareTrait;
use Netgen\BlockManager\Serializer\Values\VersionedValue;
use Netgen\BlockManager\Serializer\Values\View;
use Netgen\BlockManager\View\RendererInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;

final class ViewNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /**
     * @var \Netgen\BlockManager\View\RendererInterface
     */
    private $viewRenderer;

    public function __construct(RendererInterface $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;
    }

    /**
     * @param \Netgen\BlockManager\Serializer\Values\ViewInterface $object
     * @param string $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedData = $this->serializer->normalize(
            new VersionedValue(
                $object->getValue(),
                $object->getVersion(),
                $object->getStatusCode()
            ),
            $format,
            $context
        );

        if (!isset($context['disable_html']) || $context['disable_html'] !== true) {
            $normalizedData['html'] = $this->viewRenderer->renderValue(
                $object->getValue(),
                $object->getContext(),
                [
                    'api_version' => $object->getVersion(),
                ] + $object->getViewParameters()
            );
        }

        return $normalizedData;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof View;
    }
}
