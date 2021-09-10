<?php

namespace Pkerrigan\Xray;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since  14/05/2018
 */
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

        $data['namespace'] = 'remote';
        $data['traced']    = $this->traced;

        return array_filter($data);
    }
}
