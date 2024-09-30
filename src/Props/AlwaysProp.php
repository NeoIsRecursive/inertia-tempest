<?php

namespace NeoIsRecursive\Inertia\Props;

class AlwaysProp
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        return is_callable($this->value) ? call_user_func($this->value) : $this->value;
    }
}
