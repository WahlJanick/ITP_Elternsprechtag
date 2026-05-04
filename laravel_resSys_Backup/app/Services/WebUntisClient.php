<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WebUntisClient
{
    public function getTeachers(): array
    {
        return $this->request($this->endpoint('teacher_endpoint'));
    }

    public function getClasses(): array
    {
        return $this->request($this->endpoint('class_endpoint'));
    }

    public function getEnrollments(): array
    {
        $configuredEndpoint = config('webuntis.enrollment_endpoint');

        if (is_string($configuredEndpoint) && $configuredEndpoint !== '') {
            return $this->request($configuredEndpoint);
        }

        $schoolId = config('webuntis.school_id');

        if (! is_string($schoolId) || trim($schoolId) === '') {
            throw new RuntimeException('WEBUNTIS_SCHOOL_ID is required when WEBUNTIS_ENROLLMENT_ENDPOINT is not set.');
        }

        return $this->request("/ims/oneroster/v1p1/schools/{$schoolId}/enrollments");
    }

    private function request(string $endpoint): array
    {
        $response = $this->http()->get($this->url($endpoint));

        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(
                sprintf('WebUntis request failed for [%s] with status %s.', $endpoint, $response->status()),
                previous: $exception
            );
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new RuntimeException(sprintf('WebUntis response for [%s] was not valid JSON object/array.', $endpoint));
        }

        return $json;
    }

    private function http(): PendingRequest
    {
        $baseUrl = config('webuntis.base_url');
        $token = config('webuntis.bearer_token');

        if (! is_string($baseUrl) || $baseUrl === '') {
            throw new RuntimeException('WEBUNTIS_BASE_URL is not configured.');
        }

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('WEBUNTIS_BEARER_TOKEN is not configured.');
        }

        return Http::acceptJson()
            ->withToken($token)
            ->timeout((int) config('webuntis.timeout', 15));
    }

    private function endpoint(string $configKey): string
    {
        $endpoint = config("webuntis.{$configKey}");

        if (! is_string($endpoint) || $endpoint === '') {
            throw new RuntimeException("WebUntis endpoint [{$configKey}] is not configured.");
        }

        return $endpoint;
    }

    private function url(string $endpoint): string
    {
        $baseUrl = (string) config('webuntis.base_url');

        if (str_starts_with($endpoint, 'http://') || str_starts_with($endpoint, 'https://')) {
            return $endpoint;
        }

        return $baseUrl.'/'.ltrim($endpoint, '/');
    }
}
