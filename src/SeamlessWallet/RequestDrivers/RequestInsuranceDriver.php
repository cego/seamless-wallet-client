<?php

namespace Cego\SeamlessWallet\RequestDrivers;

use Illuminate\Support\Facades\Http;
use Cego\RequestInsurance\Models\RequestInsurance;
use Illuminate\Http\Client\Response as HttpResponse;
use function _HumbugBox373c0874430e\React\Promise\resolve;

class RequestInsuranceDriver implements RequestDriver
{
    public const OPTION_PRIORITY = 'priority';

    /**
     * Makes a request to the service asynchronously
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @param array $options
     *
     * @return Response
     */
    public function makeRequest(string $method, string $endpoint, array $data = [], array $headers = [], array $options = []): Response
    {
        $request = [
            'method'  => $method,
            'url'     => $endpoint,
            'payload' => $data,
            'headers' => $headers,
        ];

        if ($options[static::OPTION_PRIORITY]) {
            $request['priority'] = $options[static::OPTION_PRIORITY];
        }

        $requestInsurance = resolve(RequestInsurance::class);

        /** @phpstan-ignore-next-line Its a magic method from Laravel models */
        $requestInsurance::create($request);

        return new Response(0, [], false);
    }

    /**
     * Transforms a http response into the expected response class
     *
     * @param HttpResponse $httpResponse
     *
     * @return Response
     */
    protected function transformResponse(HttpResponse $httpResponse): Response
    {
        return new Response($httpResponse->status(), $httpResponse->json(), true);
    }
}
