<?php

declare(strict_types=1);

/**
 * @var NeoIsRecursive\Inertia\Views\InertiaBaseView $this
 * @var NeoIsRecursive\Inertia\PageData $page
 * @var string|null $id
 */

?>

<div id="<?= $id ?? 'app'; ?>" data-page="<?= htmlentities(json_encode($page ?? $this->page)); ?>"></div>
