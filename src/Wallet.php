<?php


namespace Immeyti\WalletClient;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Wallet
{
    private function request($endpoint, $method = 'GET', $body = '', $type = 'graphql')
    {
        /** @var Client $client */
        $client = app(Client::class);

        if ($type === 'grapql') {
            /** @var Response $response */
            $response = $client->request($method, $endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $body
            ]);
        } else {
            /** @var Response $response */
            $response = $client->request($method, $endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $body
            ]);
        }

        $response = json_decode($response->getBody(), true);


        if (key_exists('errors', $response))
            throw new \Exception($response['errors'][0]['message']);

        if (key_exists(0, $response) and key_exists('errors', $response[0]))
            throw new \Exception($response[0]['errors'][0]['message']);

        return $response;
    }

    /**
     * @param string|null $conditions
     * @return array|Response|mixed
     */
    public function allTransactions($conditions = null)
    {
        $baseQuery = '{"query": "query { allTransactions { uuid id type for amount action_type created_at} } "}';
        $graphQLquery = $conditions
            ? str_replace('$conditions', $conditions,'{"query": "query { allTransactions ($conditions) { uuid id type for amount action_type created_at} } "}')
            : $baseQuery;

        try {
            $response = $this->request('wallet.test/graphql', 'POST', $graphQLquery);

            return $response['data']['allTransactions'];

        } catch (\Exception $e) {
            return  [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function account($uuid)
    {
        $graphQLquery = '{"query": "query { allAccounts(where: {column: UUID, operator: EQ, value: $uuid}) { uuid id user_id coin_type balance blocked_balance created_at transactions { id type for amount action_type created_at }} } "}';
        $graphQLquery = str_replace('$uuid', '\"'.$uuid.'\"', $graphQLquery);

        try {
            $response = $this->request('wallet.test/graphql', 'POST', $graphQLquery);

            $account = $response['data']['allAccounts'];

            if (empty($account))
                throw new \Exception('account not found');

            return $account[0];

        } catch (\Exception $e) {
            return  [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function transactionsBetween($from, $to)
    {
        $graphQLquery = '{"query": "query { allTransactions(where: { AND:[{column: CREATEDAT, operator: GT value: $from } { column: CREATEDAT, operator: LT value: $to }]}) { uuid id type for amount action_type created_at} } "}';
        $graphQLquery = str_replace('$from', '\"'.$from.'\"', $graphQLquery);
        $graphQLquery = str_replace('$to', '\"'.$to.'\"', $graphQLquery);

        try {
            $response = $this->request('wallet.test/graphql', 'POST', $graphQLquery);

           return $response['data']['allTransactions'];
        } catch (\Exception $e) {
            return  [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function deposit($userId, $coinType, $amount)
    {
        $endPoint = 'wallet.test/api/v1/accounts/deposit';
        $params = [
            'user_id' => $userId,
            'coin_type' => $coinType,
            'amount' => $amount
        ];

        try {
            return $this->request($endPoint, 'POST', $params, 'rest');
        }catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function withdraw($userId, $coinType, $amount)
    {
        $endPoint = 'wallet.test/api/v1/accounts/withdraw';
        $params = [
            'user_id' => $userId,
            'coin_type' => $coinType,
            'amount' => $amount
        ];

        try {
            return $this->request($endPoint, 'POST', $params, 'rest');
        }catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}
