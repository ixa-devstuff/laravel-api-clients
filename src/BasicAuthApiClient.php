<?php

namespace ixaDevstuff\LaravelApiClients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class BasicAuthApiClient extends ApiClient
{
    protected array $credentialKeys = ['url', 'username', 'password'];

    /**
     * Get a request object with authentication headers.
     * @return PendingRequest
     */
    protected function getRequest(): PendingRequest
    {
        return Http::withHeaders($this->getHeaders())->withBasicAuth($this->credentials['username'], $this->credentials['password']);
    }
}
