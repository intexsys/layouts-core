<?php

namespace Netgen\Bundle\BlockManagerBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class RestExceptionListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    protected $serializer;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::EXCEPTION => 'onException');
    }

    /**
     * Serializes the exception if {@link SetIsRestRequestListener::REST_API_FLAG_NAME}
     * is set to true.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onException(GetResponseForExceptionEvent $event)
    {
        $attributes = $event->getRequest()->attributes;
        if ($attributes->get(SetIsRestRequestListener::REST_API_FLAG_NAME) !== true) {
            return;
        }

        $response = new JsonResponse();
        $response->setContent(
            $this->serializer->serialize(
                $event->getException(),
                'json',
                array('version' => 1)
            )
        );

        $event->setResponse($response);
    }
}
