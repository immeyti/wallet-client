<?php


namespace Immeyti\WalletClient;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Wallet
{
    public function request($endpoint, $method = 'GET')
    {
        try {
            $client = app(Client::class);

            /** @var Response $response */
            $response = $client->$method($endpoint);

            $response = json_decode($response->getBody(), true);

            return $response;
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage()
            ];
        }
    }
}
