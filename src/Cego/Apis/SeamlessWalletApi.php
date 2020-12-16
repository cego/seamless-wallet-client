<?php

namespace Cego\Apis;

use Cego\SeamlessWalletService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Cego\Exceptions\SeamlessWalletRequestFailedException;

/**
 * Class SeamlessWalletApi
 */
abstract class SeamlessWalletApi
{
    /** @var SeamlessWalletService */
    private SeamlessWalletService $walletService;

    /**
     * SeamlessWalletApi constructor.
     *
     * @param SeamlessWalletService $walletService
     */
    public function __construct(SeamlessWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Performs a post request
     *
     * @param string $endpoint
     * @param array $data
     *
     * @throws SeamlessWalletRequestFailedException
     */
    protected function postRequest(string $endpoint, array $data = []): void
    {
        $this->makeRequest('post', $this->walletService->getFullEndpointUrl($endpoint), $data);
    }

    /**
     * Performs a get request
     *
     * @param string $endpoint
     *
     * @return array
     * @throws SeamlessWalletRequestFailedException
     *
     */
    protected function getRequest(string $endpoint): array
    {
        return $this->makeRequest('get', $this->walletService->getFullEndpointUrl($endpoint))->json();
    }

    /**
     * Makes a request to the service
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     *
     * @return Response
     * @throws SeamlessWalletRequestFailedException
     *
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        $maxTries = 10;
        $try = 0;
        $endpoint = $this->transformEndpoint($endpoint);

        do {
            $response = Http::$method($this->walletService->getFullEndpointUrl($endpoint), $data);

            if ($response->successful()) {
                return $response;
            }

            // Do not retry client errors
            if ($response->clientError()) {
                throw new SeamlessWalletRequestFailedException($response);
            }

            // Wait 0.2 sec before trying again, if server error
            usleep(200000);
        } while ($try < $maxTries);

        throw new SeamlessWalletRequestFailedException($response);
    }

    /**
     * Method child classes can use to transform the endpoint url before execution
     *      Used for replacing placeholders with keys like the users id.
     *
     * @param string $endpoint
     *
     * @return string
     */
    protected function transformEndpoint(string $endpoint): string
    {
        return $endpoint;
    }
}
