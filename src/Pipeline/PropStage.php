<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline;

interface PropStage
{
    public function __invoke(PropPipelineContext $context): PropPipelineContext;
}
