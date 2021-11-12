<?php

namespace Fido\PHPXray;

class HttpSegment extends RemoteSegment
{
    use HttpTrait;

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data['http'] = $this->serialiseHttpData();

        return array_filter($data);
    }
}
