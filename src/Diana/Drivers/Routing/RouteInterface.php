<?php

namespace Diana\Drivers\Routing;

interface RouteInterface
{
    public function getMiddleware(): array;

    public function getController(): string;

    public function getMethod(): string;

    public function setParameters(array $params): void; // TODO: Is this actually the right place to store parameters?
    public function getParameters(): array;

    public function setSegments(array $segments): void;
    public function getSegments(): array;
    public function toArray(): array;
}
