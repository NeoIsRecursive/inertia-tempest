<?php

namespace NeoIsRecursive\Inertia\Props;

class LazyProp
{
    public function __construct(public $callback) {}

    public function __invoke()
    {
        return call_user_func($this->callback);
    }
}
