<?php

namespace Diana\Drivers;

interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    public function get(string $abstract): mixed;
    public function has(string $abstract): bool;
    public function make(string $abstract, array $parameters = []): mixed;
    public function call(callable|string $callback, array $parameters = [], $defaultMethod = null): mixed;
    public function instance(string $abstract, mixed $instance): void;
    public function singleton(string $abstract, mixed $concrete = null): void;
}
