<?php

namespace Netgen\Bundle\BlockManagerBundle\EventListener\CsrfValidation;

use Netgen\Bundle\BlockManagerBundle\EventListener\SetIsApiRequestListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class ApiCsrfValidationListener extends CsrfValidationListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $attributes = $event->getRequest()->attributes;
        if ($attributes->get(SetIsApiRequestListener::API_FLAG_NAME) !== true) {
            return;
        }

        parent::onKernelRequest($event);
    }
}
