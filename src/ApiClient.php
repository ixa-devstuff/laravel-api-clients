<?php

namespace ixaDevstuff\LaravelApiClients;

use ixaDevstuff\LaravelApiClients\Exceptions\ApiClientConfigException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Str;

abstract class ApiClient
{
    protected array $credentials = [];
    protected array $credentialKeys = ['url'];

    /**
     * @throws ApiClientConfigException
     */
    public function initClient(array $credentials): void
    {
        $this->loadApiCredentials($credentials);
    }

    /**
     * Build full url from uri & credentials url.
     * @param string $uri
     * @return string
     */
    protected function buildUrl(string $uri): string
    {
        return Str::of($this->credentials['url'])->rtrim('/')
            ->append('/')
            ->append(Str::of($uri)->ltrim('/'));
    }

    /**
     * Get a request object with authentication headers.
     * @return PendingRequest
     */
    protected function getRequest(): PendingRequest
    {
        return Http::withHeaders($this->getHeaders());
    }

    /**
     * Send a get request and return response.
     *
     * @param string $uri
     * @param array $queryParams
     * @return Response
     */
    public function get(string $uri, array $queryParams = []): Response
    {
        return $this->getRequest()->get($this->buildUrl($uri), $queryParams);
    }

    /**
     * Send a post request and return contents array.
     *
     * @param string $uri
     * @param array $postParams
     * @param bool $asForm
     * @return Response
     */
    public function post(string $uri, array $postParams = [], bool $asForm = false): Response
    {
        $request = $this->getRequest();
        if ($asForm) {
            $request->asForm();
        }
        return $request->post($this->buildUrl($uri), $postParams);
    }

    /**
     * Send a delete request and return response.
     *
     * @param string $uri
     * @param array $deleteParams
     * @return Response
     */
    public function delete(string $uri, array $deleteParams = []): Response
    {
        return $this->getRequest()->delete($this->buildUrl($uri), $deleteParams);
    }

    /**
     * Load the API credentials.
     *
     * @param array $credentials
     * @return void
     * @throws ApiClientConfigException
     */
    protected function loadApiCredentials(array $credentials): void
    {
        // Check to ensure all credential keys are present
        foreach ($this->credentialKeys as $key) {
            if (empty($credentials[$key])) {
                throw new ApiClientConfigException('Could not load credentials for API client: ' . get_class($this));
            }
        }

        $this->credentials = $credentials;
    }

    /**
     * Get headers required for API request.
     * @return array
     */
    protected function getHeaders(): array
    {
        return [
            'Accept'        => 'application/json',
            'content-type'  => 'application/json',
        ];
    }

    /**
     * Extract response data as array.
     *
     * @param Response $response
     * @return array
     */
    protected function extractResponseData(Response $response): array
    {
        return (array) json_decode($response->getBody()->getContents(), true);
    }
}
