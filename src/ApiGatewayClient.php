<?php

namespace ixaDevstuff\LaravelApiClients;

use ixaDevstuff\LaravelApiClients\Exceptions\ApiClientConfigException;
use ixaDevstuff\LaravelApiClients\Exceptions\ApiGatewayResponseException;
use Illuminate\Http\Client\Response;

class ApiGatewayClient extends ApiClient
{
    protected array $credentialKeys = ['url', 'api_key'];

    /**
     * @throws ApiClientConfigException
     */
    public function __construct(array $config)
    {
        $this->initClient($config);
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
            'x-api-key'     => $this->credentials['api_key'],
        ];
    }

    /**
     * Validate API gateway response and return response data.
     *
     * @param Response $response
     * @return array
     * @throws ApiGatewayResponseException
     */
    protected function validateAndReturnResponseData(Response $response): array
    {
        $data = $this->extractResponseData($response);

        if (!$response->successful()) {
            Log::error('API Gateway Error', [
                'uri' => $response->effectiveUri(),
                'status' => $response->status(),
                'error' => $data['message'] ?? '',
                'headers' => $response->headers(),
            ]);
            
            $message = $data['message'] ?? 'API responded with status code: ' . $response->status();
            throw new ApiGatewayResponseException($message, $response->status());
        }

        if (isset($data['message']) && $data['message'] === 'Internal server error') {
            throw new ApiGatewayResponseException($data['message'], 500);
        }

        return $data;
    }
}
