<?php

namespace Fido\PHPXray;

class RemoteSegment extends Segment
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

        $data[DictionaryInterface::SEGMENT_KEY_MAIN_NAMESPACE] = DictionaryInterface::SEGMENT_ENUM_NAMESPACE_REMOTE;
        $data[DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_TRACED] = $this->traced;

        return \array_filter($data);
    }
}
