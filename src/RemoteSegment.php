<?php

namespace Fido\PHPXray;

class RemoteSegment extends Segment implements Typed
{
    protected bool $traced = false;

    public function setTraced(bool $traced): self
    {
        $this->traced = $traced;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[self::SEGMENT_KEY_MAIN_NAMESPACE] = self::SEGMENT_ENUM_NAMESPACE_REMOTE;
        $data[self::SEGMENT_KEY_HTTP_REQUEST_TRACED] = $this->traced;

        return \array_filter($data);
    }
}
