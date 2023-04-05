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

    public static function fromThrowable(\Throwable $throwable, bool $isRemote = false): self
    {
        $exceptions = [];
        $paths = [];

        $workingDirectory = $throwable->getFile() . "::" . $throwable->getLine();

        do {
            $stack = [];

            foreach ($throwable->getTrace() as $trace) {

                $line = $trace['line'] ?? 0;
                $file = $trace['file'] ?? 'no_file';
                $class = $trace['class'] ?? 'no_class';
                $type = $trace['type'] ?? 'no_type';
                $function = $trace['function'];
                $args = $trace['args'] ?? [];
                try {
                    $encodedArgs = \count($args) > 0 ? ' with args: ' . \json_encode($args) : '';
                } catch (\Throwable $t) {
                    $encodedArgs = " with args: [unable to encode] {$t->getFile()} {$t->getLine()}";
                }

                $stack[] = new CauseStackFrame(path: $file, line: $line, label: $class . $type . $function . $encodedArgs);
            }

            $exceptions[] = new CauseException(
                message: $throwable->getMessage(),
                type: \get_class($throwable),
                remote: $isRemote,
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
            DictionaryInterface::SEGMENT_KEY_CAUSE_PATHS => $this->paths,
            DictionaryInterface::SEGMENT_KEY_CAUSE_EXCEPTIONS => $this->exceptions,
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
