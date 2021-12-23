<?php

namespace Fido\PHPXray;

class SqlSegment extends RemoteSegment
{
    public function __construct(
        string $name,
        protected string $query,
        protected ?string $url = null,
        protected ?string $preparation = null,
        protected ?string $databaseType = null,
        protected ?string $databaseVersion = null,
        protected ?string $driverVersion = null,
        protected ?string $user = null,
        bool $traced = false,
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
            traced: $traced,
            parentId: $parentId,
            traceId: $traceId,
            error: $error,
            fault: $fault,
            cause: $cause,
            independent: $independent,
            lastOpenSegment: $lastOpenSegment,
        );
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[DictionaryInterface::SEGMENT_KEY_MAIN_SQL] = \array_filter([
            DictionaryInterface::SEGMENT_KEY_SQL_URL              => $this->url,
            DictionaryInterface::SEGMENT_KEY_SQL_PREPARATION      => $this->preparation,
            DictionaryInterface::SEGMENT_KEY_SQL_DATABASE_TYPE    => $this->databaseType,
            DictionaryInterface::SEGMENT_KEY_SQL_DATABASE_VERSION => $this->databaseVersion,
            DictionaryInterface::SEGMENT_KEY_SQL_DRIVER_VERSION   => $this->driverVersion,
            DictionaryInterface::SEGMENT_KEY_SQL_USER             => $this->user,
            DictionaryInterface::SEGMENT_KEY_SQL_SANITIZED_QUERY  => $this->query,
        ]);

        return $data;
    }
}
