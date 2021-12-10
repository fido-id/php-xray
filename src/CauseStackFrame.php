<?php

namespace Fido\PHPXray;

/**
 * All fields are optional.
 * - path – The relative path to the file.
 * - line – The line in the file.
 * - label – The function or method name.
 */
class CauseStackFrame implements \JsonSerializable
{
    public function __construct(
        protected string $path,
        protected int $line,
        protected string $label,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            DictionaryInterface::SEGMENT_KEY_CAUSE_STACK_FRAME_PATH  => $this->path,
            DictionaryInterface::SEGMENT_KEY_CAUSE_STACK_FRAME_LINE  => $this->line,
            DictionaryInterface::SEGMENT_KEY_CAUSE_STACK_FRAME_LABEL => $this->label,
        ];
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
