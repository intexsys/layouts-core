<?php

declare(strict_types=1);

namespace Netgen\Layouts\Event;

final class LayoutsEvents
{
    /**
     * This event will be dispatched when the view object is being built.
     *
     * In addition to this event, every view has its own event, e.g. for block,
     * the event is called "ngbm.view.build_view.block" that is being dispatched
     * after the main event.
     */
    public const BUILD_VIEW = 'ngbm.view.build_view';

    /**
     * This event will be dispatched when the view object is being rendered.
     *
     * In addition to this event, every view has its own event, e.g. for block,
     * the event is called "ngbm.view.render_view.block" that is being dispatched
     * after the main event.
     */
    public const RENDER_VIEW = 'ngbm.view.render_view';
}
