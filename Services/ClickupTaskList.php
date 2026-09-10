<?php

namespace Modules\ClickupIntegration\Services;

/**
 * Wraps a single ClickUp list, mirroring the subset of
 * ClickUpClient\Models\TaskList the module used from the retired
 * edsol/php-clickup-api-client package.
 */
class ClickupTaskList
{
    private ClickupClient $client;

    private string $id;

    public function __construct(ClickupClient $client, string $id)
    {
        $this->client = $client;
        $this->id = $id;
    }

    public function members()
    {
        return $this->client->get("list/{$this->id}/member");
    }

    public function addTask(array $data)
    {
        return $this->client->post("list/{$this->id}/task", $data);
    }
}
