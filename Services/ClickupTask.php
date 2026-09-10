<?php

namespace Modules\ClickupIntegration\Services;

/**
 * Wraps a single ClickUp task, mirroring the subset of
 * ClickUpClient\Models\Task the module used from the retired
 * edsol/php-clickup-api-client package.
 */
class ClickupTask
{
    private ClickupClient $client;

    private string $id;

    public function __construct(ClickupClient $client, string $id)
    {
        $this->client = $client;
        $this->id = $id;
    }

    public function setCustomField(string $fieldId, $value)
    {
        return $this->client->post("task/{$this->id}/field/{$fieldId}", ['value' => $value]);
    }

    public function deleteCustomField(string $fieldId)
    {
        return $this->client->delete("task/{$this->id}/field/{$fieldId}");
    }

    public function addAttachment(ClickupAttachment $attachment)
    {
        return $this->client->multipart("task/{$this->id}/attachment", $attachment->toArray());
    }

    public function createComment(array $body)
    {
        return $this->client->post("task/{$this->id}/comment", $body);
    }
}
