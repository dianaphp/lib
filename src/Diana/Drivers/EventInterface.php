<?php

namespace Diana\Drivers;

interface EventInterface
{
    public function listen(string $action, callable $callback): void;

    public function fire(string $action, mixed $payload = null): void;
}
