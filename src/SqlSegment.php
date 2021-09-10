<?php

namespace Pkerrigan\Xray;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since  14/05/2018
 */
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

        $data['sql'] = array_filter([
            'url'              => $this->url ?? null,
            'preparation'      => $this->preparation ?? null,
            'database_type'    => $this->databaseType ?? null,
            'database_version' => $this->databaseVersion ?? null,
            'driver_version'   => $this->driverVersion ?? null,
            'user'             => $this->user ?? null,
            'sanitized_query'  => $this->query ?? null,
        ]);

        return $data;
    }
}
