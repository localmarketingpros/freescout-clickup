<?php

namespace Modules\ClickupIntegration\Services;

use GuzzleHttp\Client as GuzzleClient;

/**
 * Thin ClickUp API v2 client built on FreeScout's global Guzzle client.
 *
 * Replaces the edsol/php-clickup-api-client package this module used to
 * bundle, keeping only the surface ClickupService actually calls. Guzzle
 * raises its own exception for any non-2xx response (http_errors is on by
 * default), so callers can catch that the same way they caught the old
 * client's exceptions.
 */
class ClickupClient
{
    private GuzzleClient $http;

    public function __construct(string $apiToken)
    {
        $this->http = new GuzzleClient([
            'base_uri' => 'https://api.clickup.com/api/v2/',
            'headers' => [
                'Authorization' => $apiToken,
            ],
        ]);
    }

    public function get(string $path, array $params = [])
    {
        $response = $this->http->request('GET', $path, ['query' => $params]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function post(string $path, array $body = [])
    {
        $response = $this->http->request('POST', $path, ['json' => $body]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function put(string $path, array $body = [])
    {
        $response = $this->http->request('PUT', $path, ['json' => $body]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function delete(string $path)
    {
        $response = $this->http->request('DELETE', $path);

        return $response->getStatusCode();
    }

    /**
     * Uploads a single multipart part (used for the task attachment endpoint).
     *
     * @param array $part one Guzzle multipart entry, e.g. ['name' => ..., 'contents' => ..., 'filename' => ...]
     */
    public function multipart(string $path, array $part)
    {
        $response = $this->http->request('POST', $path, ['multipart' => [$part]]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function task(string $id): ClickupTask
    {
        $this->requireId($id);

        return new ClickupTask($this, $id);
    }

    public function taskList(string $id): ClickupTaskList
    {
        $this->requireId($id);

        return new ClickupTaskList($this, $id);
    }

    /**
     * The original client refused to build a request without an ID.
     * Keep that guard so a blank ID fails here with a clear message
     * instead of leaving as a malformed HTTP call.
     */
    private function requireId(string $id): void
    {
        if (trim($id) === '') {
            throw new \Exception('An ID is required to make the request', 1);
        }
    }
}
