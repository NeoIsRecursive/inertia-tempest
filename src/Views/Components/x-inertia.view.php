<?php

declare(strict_types=1);

/**
 * @var NeoIsRecursive\Inertia\Views\InertiaBaseView $this
 * @var NeoIsRecursive\Inertia\PageData|null $page
 * @var string|null $id
 */

$id ??= 'app';
?>

<script data-page="<?= $id ?>" type="application/json">
<?= \Tempest\Support\Json\encode($page ?? $this->page) ?>
</script>
<div id="<?= $id ?>"></div>
