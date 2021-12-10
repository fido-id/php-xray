<?php

namespace Fido\PHPXray;

interface HttpInterface
{
    /**
     * @return array<string, mixed>
     */
    public function serializeHttpData(): array;
}
