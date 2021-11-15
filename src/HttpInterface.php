<?php

namespace Fido\PHPXray;

interface HttpInterface
{
    public function setUrl(string $url): self;

    public function setMethod(string $method): self;

    public function setResponseCode(int $responseCode): self;

    /**
     * @return array<string, mixed>
     */
    public function serialiseHttpData(): array;
}
