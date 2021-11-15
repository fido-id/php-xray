<?php

namespace Fido\PHPXray;

class SqlSegment extends RemoteSegment
{
    protected string $url;
    protected string $preparation;
    protected string $databaseType;
    protected string $databaseVersion;
    protected string $driverVersion;
    protected string $user;
    protected string $query;

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function setPreparation(string $preparation): self
    {
        $this->preparation = $preparation;

        return $this;
    }

    public function setDatabaseType(string $databaseType): self
    {
        $this->databaseType = $databaseType;

        return $this;
    }

    public function setDatabaseVersion(string $databaseVersion): self
    {
        $this->databaseVersion = $databaseVersion;

        return $this;
    }

    public function setDriverVersion(string $driverVersion): self
    {
        $this->driverVersion = $driverVersion;

        return $this;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[DictionaryInterface::SEGMENT_KEY_MAIN_SQL] = \array_filter([
            DictionaryInterface::SEGMENT_KEY_SQL_URL              => $this->url ?? null,
            DictionaryInterface::SEGMENT_KEY_SQL_PREPARATION      => $this->preparation ?? null,
            DictionaryInterface::SEGMENT_KEY_SQL_DATABASE_TYPE    => $this->databaseType ?? null,
            DictionaryInterface::SEGMENT_KEY_SQL_DATABASE_VERSION => $this->databaseVersion ?? null,
            DictionaryInterface::SEGMENT_KEY_SQL_DRIVER_VERSION   => $this->driverVersion ?? null,
            DictionaryInterface::SEGMENT_KEY_SQL_USER             => $this->user ?? null,
            DictionaryInterface::SEGMENT_KEY_SQL_SANITIZED_QUERY  => $this->query ?? null,
        ]);

        return $data;
    }
}
