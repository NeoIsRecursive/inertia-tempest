<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline\Stages;

use NeoIsRecursive\Inertia\Contracts\Onceable;
use NeoIsRecursive\Inertia\Pipeline\PropPipelineContext;
use NeoIsRecursive\Inertia\Pipeline\PropStage;
use NeoIsRecursive\Inertia\Support\Header;

use function Tempest\Support\arr;

final readonly class ResolveOnceProps implements PropStage
{
    public function __invoke(PropPipelineContext $context): PropPipelineContext
    {
        $only = array_filter(explode(
            separator: ',',
            string: $context->request->headers->get(Header::PARTIAL_ONLY) ?? '',
        ));
        $except = array_filter(explode(
            separator: ',',
            string: $context->request->headers->get(Header::PARTIAL_EXCEPT) ?? '',
        ));

        $onceProps = arr($context->originalProps)
            ->filter(static fn($prop) => $prop instanceof Onceable && $prop->shouldResolveOnce())
            ->filter(static function ($_, string|int $key) use ($context, $only, $except): bool {
                if (!$context->isPartial()) {
                    return true;
                }

                if (!is_string($key)) {
                    return false;
                }

                if ($only !== [] && !in_array($key, $only, true)) {
                    return false;
                }

                return !in_array($key, $except, true);
            })
            ->map(static fn(Onceable $prop, string|int $key) => [
                'key' => $prop->getKey() ?? (string) $key,
                'value' => [
                    'prop' => (string) $key,
                    'expiresAt' => $prop->expiresAt(),
                ],
            ])
            ->reduce(static function (array $acc, array $item): array {
                $acc[$item['key']] = $item['value'];

                return $acc;
            }, []);

        return $context->with(['onceProps' => $onceProps === [] ? null : $onceProps]);
    }
}
