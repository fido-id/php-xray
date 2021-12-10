<?php

namespace Fido\PHPXray;

use JsonSerializable;

/**
 * - working_directory – The full path of the working directory when the exception occurred.
 * - paths – The array of paths to libraries or modules in use when the exception occurred.
 * - exceptions – The array of exception objects.
 */
class Cause implements JsonSerializable
{
    /**
     * @param string[] $paths
     * @param CauseException[] $exceptions
     */
    public function __construct(
        protected string $workingDirectory,
        protected array  $paths,
        protected array  $exceptions,
    )
    {
    }

    public static function fromThrowable(\Throwable $throwable): self
    {
        $exceptions = [];
        $paths = [];

        $workingDirectory = $throwable->getFile() . "::" . $throwable->getLine();

        do {
            $stack = [];

            foreach ($throwable->getTrace() as $trace) {
                $stack[] = new CauseStackFrame(
                    path: $trace['file'],
                    line: $trace['line'],
                    label: $trace['class'] . $trace['type'] . $trace['function'] . (\count($trace['args']) > 0 ? " with args: " . \json_encode($trace['args']) : ""),
                );
            }

            $exceptions[] = new CauseException(
                message: $throwable->getMessage(),
                type: \get_class($throwable),
                remote: false,
                truncated: 0,
                skipped: 0,
                stack: $stack
            );

            $paths[] = $throwable->getFile() . "::" . $throwable->getLine();

            $throwable = $throwable->getPrevious();

        } while ($throwable !== null);

        return new self(
            workingDirectory: $workingDirectory,
            paths: $paths,
            exceptions: $exceptions
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            DictionaryInterface::SEGMENT_KEY_CAUSE_WORKING_DIRECTORY => $this->workingDirectory,
            DictionaryInterface::SEGMENT_KEY_CAUSE_PATHS             => $this->paths,
            DictionaryInterface::SEGMENT_KEY_CAUSE_EXCEPTIONS        => $this->exceptions,
        ];
    }

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * @return string[]
     */
    public function getPaths(): array
    {
        return $this->paths;
    }


    /**
     * @return CauseException[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
