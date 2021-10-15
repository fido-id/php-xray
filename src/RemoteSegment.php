<?php

namespace Fido\PHPXray;

class RemoteSegment extends Segment
{
    /**
     * @var bool
     */
    protected $traced = false;

    /**
     * @param bool $traced
     * @return static
     */
    public function setTraced(bool $traced): RemoteSegment
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

        $data['namespace'] = 'remote';
        $data['traced'] = $this->traced;

        return array_filter($data);
    }
}
