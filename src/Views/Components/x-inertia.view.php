<?php

declare(strict_types=1);

/**
 * @var NeoIsRecursive\Inertia\Views\InertiaBaseView $this
 * @var NeoIsRecursive\Inertia\PageData|null $page
 * @var string|null $id
 */

?>

<div id="<?= $id ?? 'app'; ?>" data-page="<?= htmlentities(\Tempest\Support\Json\encode($page ?? $this->page)) ?>"></div>
