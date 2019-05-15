<?php

declare(strict_types=1);

namespace Netgen\Layouts\Behat\Page\Admin\Layouts;

use Netgen\Layouts\Behat\Page\Admin\AdminPage;

final class IndexPage extends AdminPage
{
    public function getRouteName(): string
    {
        return 'nglayouts_admin_layouts_index';
    }

    public function createLayout(): void
    {
        $this->getElement('create_new_layout')->click();
    }

    public function editLayout(string $layoutName): void
    {
        $this->getElement('actions_dropdown', ['%layout-name%' => $layoutName])->press();
        $this->getElement('edit_layout_action', ['%layout-name%' => $layoutName])->click();
    }

    public function clickLayoutName(string $layoutName): void
    {
        $this->getElement('layout_name', ['%layout-name%' => $layoutName])->click();
    }

    public function openDuplicateLayoutModal(string $layoutName): void
    {
        $this->openModal(
            function () use ($layoutName): void {
                $this->getElement('actions_dropdown', ['%layout-name%' => $layoutName])->press();
                $this->getElement('copy_layout_action', ['%layout-name%' => $layoutName])->press();
            }
        );
    }

    public function openDeleteLayoutModal(string $layoutName): void
    {
        $this->openModal(
            function () use ($layoutName): void {
                $this->getElement('actions_dropdown', ['%layout-name%' => $layoutName])->press();
                $this->getElement('delete_layout_action', ['%layout-name%' => $layoutName])->press();
            }
        );
    }

    public function layoutExists(string $layoutName): bool
    {
        return $this->hasElement('layout', ['%layout-name%' => $layoutName]);
    }

    public function nameDuplicatedLayout(string $layoutName): void
    {
        $this->getDocument()->fillField('copy_name', $layoutName);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(
            parent::getDefinedElements(),
            [
                'layout' => '.nl-layout [data-name="%layout-name%"]',
                'layout_name' => '.nl-layout-name a:contains("%layout-name%")',

                'create_new_layout' => 'a#add-new-button',

                'actions_dropdown' => '.nl-layout [data-name="%layout-name%"] button.nl-dropdown-toggle',
                'edit_layout_action' => '.nl-layout [data-name="%layout-name%"] a.js-layout-edit',
                'copy_layout_action' => '.nl-layout [data-name="%layout-name%"] button.js-layout-copy',
                'delete_layout_action' => '.nl-layout [data-name="%layout-name%"] button.js-layout-delete',
            ]
        );
    }
}
