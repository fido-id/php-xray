<?php

namespace Fido\PHPXray;

class RemoteSegment extends Segment
{
    public function __construct(
        string $name,
        protected bool $traced = false,
        ?string $parentId = null,
        ?string $traceId = null,
        bool $error = false,
        bool $fault = false,
        ?Cause $cause = null,
        bool $independent = false,
        int $lastOpenSegment = 0
    ) {
        parent::__construct(
            name: $name,
            parentId: $parentId,
            traceId: $traceId,
            error: $error,
            fault: $fault,
            cause: $cause,
            independent: $independent,
            lastOpenSegment: $lastOpenSegment,
        );
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[DictionaryInterface::SEGMENT_KEY_MAIN_NAMESPACE]      = DictionaryInterface::SEGMENT_ENUM_NAMESPACE_REMOTE;
        $data[DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_TRACED] = $this->traced;

        return \array_filter($data);
    }
}
