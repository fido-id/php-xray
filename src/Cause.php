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
     * @param string[]         $paths
     * @param CauseException[] $exceptions
     */
    public function __construct(
        protected string $workingDirectory,
        protected array $paths,
        protected array $exceptions,
    ) {
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

    public function setWorkingDirectory(string $workingDirectory): void
    {
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * @return string[]
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @param string[] $paths
     */
    public function setPaths(array $paths): void
    {
        $this->paths = $paths;
    }

    /**
     * @return CauseException[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @param CauseException[] $exceptions
     */
    public function setExceptions(array $exceptions): void
    {
        $this->exceptions = $exceptions;
    }
}
