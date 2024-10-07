<?php

namespace Diana\Drivers\Routing;

interface RouterInterface
{
    public function registerController(string ...$controllers): RouterInterface;

    public function resolve(RequestInterface $request): RouteInterface;

    public function getErrorRoute(): ?RouteInterface;

    public function getErrorCommandRoute(): ?RouteInterface;
}
