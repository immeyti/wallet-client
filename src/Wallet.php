<?php


namespace Immeyti\WalletClient;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Wallet
{
    public function request($endpoint, $method = 'GET', $body = '')
    {
        /** @var Client $client */
        $client = app(Client::class);

        /** @var Response $response */
        $response = $client->request($method, $endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $body
        ]);

        $response = json_decode($response->getBody(), true);


        if (key_exists('errors', $response))
            throw new \Exception($response['errors'][0]['message']);

        /*if (key_exists(0, $response) and key_exists('errors', $response[0]))
            throw new \Exception($response[0]['errors'][0]['message']);*/

        return $response;
    }

    /**
     * @param null|string $accountUuid
     * @return array|Response|mixed
     */
    public function allTransactions($accountUuid = null)
    {
        $graphQLquery = '{"query": "query { allTransactions { uuid id type for amount action_type created_at} } "}';

        try {
            $response = $this->request('wallet.test/graphql', 'POST', $graphQLquery);

            return $response['data']['allTransactions'];

        } catch (\Exception $e) {
            return  [
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
}
