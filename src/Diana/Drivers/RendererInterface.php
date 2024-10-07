<?php

namespace Diana\Drivers;

interface RendererInterface
{
    public function render(string $input, array $data = []): string;
}
