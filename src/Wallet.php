<?php


namespace Immeyti\WalletClient;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Wallet
{
    public function request($endpoint, $method = 'GET', $body = '', $headers = [])
    {
        try {
            /** @var Client $client */
            $client = app(Client::class);

            /** @var Response $response */
            $response = $client->request($method, $endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => $body
            ]);

            $response = json_decode($response->getBody(), true);

            if (key_exists('errors', $response))
                throw new \Exception($response['message']);

            return $response;
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage()
            ];
        }
    }

    public function allTransactions()
    {
        $graphQLquery = '{"query": "query {
  allTransactions(
    where: {column: UUID, operator: EQ, value: "b52982ba-864e-3c94-bdf6-19460a109fa7"}
  ) {
    	uuid
    	id
      type
      for
      amount
      action_type
      created_at
  }
} "}';


        return $this->request('wallet.test/graphql', 'POST', $graphQLquery);
    }
}
