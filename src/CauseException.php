<?php

namespace Fido\PHPXray;

/**
 * All fields are optional except id.
 * - id – A 64-bit identifier for the exception, unique among segments in the same trace, in 16 hexadecimal digits.
 * - message – The exception message.
 * - type – The exception type.
 * - remote – boolean indicating that the exception was caused by an error returned by a downstream service.
 * - truncated – integer indicating the number of stack frames that are omitted from the stack.
 * - skipped – integer indicating the number of exceptions that were skipped between this exception and its child,
 *      that is, the exception that it caused.
 * - cause – Exception ID of the exception's parent, that is, the exception that caused this exception.
 * - stack – array of CauseStackFrame objects.
 */
class CauseException implements \JsonSerializable
{
    protected string $id;

    /**
     * @param CauseStackFrame[] $stack
     */
    public function __construct(
        protected string $message,
        protected string $type,
        protected bool $remote,
        protected int $truncated,
        protected int $skipped,
        protected ?string $cause = null,
        protected ?array $stack = null,
    ) {
        $this->id = bin2hex(random_bytes(8));
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            DictionaryInterface::SEGMENT_KEY_CAUSE_EXCEPTION_ID        => $this->id,
            DictionaryInterface::SEGMENT_KEY_CAUSE_EXCEPTION_MESSAGE   => $this->message,
            DictionaryInterface::SEGMENT_KEY_CAUSE_EXCEPTION_TYPE      => $this->type,
            DictionaryInterface::SEGMENT_KEY_CAUSE_EXCEPTION_REMOTE    => $this->remote,
            DictionaryInterface::SEGMENT_KEY_CAUSE_EXCEPTION_TRUNCATED => $this->truncated,
            DictionaryInterface::SEGMENT_KEY_CAUSE_EXCEPTION_SKIPPED   => $this->skipped,
            DictionaryInterface::SEGMENT_KEY_CAUSE_EXCEPTION_CAUSE     => $this->cause,
            DictionaryInterface::SEGMENT_KEY_CAUSE_EXCEPTION_STACK     => $this->stack,
        ];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRemote(): bool
    {
        return $this->remote;
    }

    public function getTruncated(): int
    {
        return $this->truncated;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getCause(): ?string
    {
        return $this->cause;
    }

    /**
     * @return CauseStackFrame[]
     */
    public function getStack(): ?array
    {
        return $this->stack;
    }
}
