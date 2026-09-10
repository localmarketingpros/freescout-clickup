<?php

namespace Modules\ClickupIntegration\Services;

use InvalidArgumentException;

/**
 * Multipart payload for the ClickUp "add attachment" endpoint.
 *
 * Replaces ClickUpClient\Objects\Attachment from the retired
 * edsol/php-clickup-api-client package.
 */
class ClickupAttachment
{
    /** @var resource */
    private $contents;

    private string $filename;

    public function __construct(array $args)
    {
        if (!is_string($args['filename'] ?? null)) {
            throw new InvalidArgumentException('`filename` must be a string');
        }
        if (!is_resource($args['contents'] ?? null)) {
            throw new InvalidArgumentException('`contents` must be a resource');
        }

        $this->contents = $args['contents'];
        $this->filename = $args['filename'];
    }

    public function toArray(): array
    {
        return [
            'name' => 'attachment',
            'contents' => $this->contents,
            'filename' => $this->filename,
        ];
    }
}
