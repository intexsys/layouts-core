<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\Event;

use Netgen\Layouts\Utils\BackwardsCompatibility\Event;
use Symfony\Component\HttpFoundation\Request;

final class AdminMatchEvent extends Event
{
    /**
     * The request the kernel is currently processing.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * The request type the kernel is currently processing.  One of
     * HttpKernelInterface::MASTER_REQUEST and HttpKernelInterface::SUB_REQUEST.
     *
     * @var int
     */
    private $requestType;

    /**
     * Pagelayout template to be used by admin interface.
     *
     * @var string|null
     */
    private $pageLayoutTemplate;

    public function __construct(Request $request, int $requestType)
    {
        $this->request = $request;
        $this->requestType = $requestType;
    }

    /**
     * Returns the request the kernel is currently processing.
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Returns the request type the kernel is currently processing.
     */
    public function getRequestType(): int
    {
        return $this->requestType;
    }

    /**
     * Sets the pagelayout template which will be used for admin interface.
     */
    public function setPageLayoutTemplate(string $template): void
    {
        $this->pageLayoutTemplate = $template;
    }

    /**
     * Returns the pagelayout template which will be used for admin interface
     * or null if no template has been set.
     */
    public function getPageLayoutTemplate(): ?string
    {
        return $this->pageLayoutTemplate;
    }
}
