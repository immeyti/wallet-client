<?php


namespace Immeyti\WalletClient\Tests;

use Illuminate\Support\Carbon;
use Immeyti\WalletClient\Tests\FakeGuzzleClientResponses;
use Immeyti\WalletClient\Wallet;

class WalletTest extends TestCase
{
    use FakeGuzzleClientResponses;

    public $wallet;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->wallet = new Wallet();
    }

    /** @test */
    public function itShouldReturnErrorMessageWhenFailedRequest()
    {
        $this->fakeGuzzleFailResponse();

        $wallet = new Wallet();
        $allTransactions = $wallet->allTransactions();

        $this->assertTrue(is_array($allTransactions));
        $this->assertArrayHasKey('message', $allTransactions);
    }

    /** @test */
    public function itShouldReturnAnArrayOfAllTransactions()
    {
        $this->fakeGuzzleTransactionsResponse();

        $wallet = new Wallet();
        $allTransactions = $wallet->allTransactions();

        $this->assertTrue(is_array($allTransactions));
        $this->assertTrue(key_exists('uuid', $allTransactions[0]));
        $this->assertTrue(key_exists('type', $allTransactions[0]));
        $this->assertTrue(key_exists('id', $allTransactions[0]));
    }

    /** @test */
    public function itCanGetManualQueryForAllTransactions()
    {
        $this->fakeGuzzleTransactionsResponse();
        $query = 'where: {column: UUID, operator: EQ, value: \"b52982ba-864e-3c94-bdf6-19460a109fa7\"}';

        $wallet = new Wallet();
        $allTransactions = $wallet->allTransactions($query);

        $this->assertTrue(is_array($allTransactions));
        $this->assertArrayNotHasKey('error', $allTransactions);
    }

    /** @test */
    public function itShouldReturnErrorMessageWhenFailedAllTransactiosGraphqlRequest()
    {
        $this->fakeGuzzleFailGraphqlResponse();

        $wallet = new Wallet();
        $allTransactions = $wallet->allTransactions();

        $this->assertTrue(is_array($allTransactions));
        $this->assertSame(
            $allTransactions,
            [
                'error' => true,
                'message' => "Cannot query field \"idsdf\" on type \"Transaction\"."
            ]
        );
    }

    /** @test */
    public function itShouldReturnAnAccount()
    {
        $this->fakeGuzzleAnAccountResponse();

        $accountUuid = 'a673086b-44eb-3850-a26e-f2baca1619f4';

        $response = $this->wallet->account($accountUuid);

        $this->assertEquals($accountUuid, $response['uuid']);
        $this->assertArrayHasKey('transactions', $response);
        $this->assertIsArray($response['transactions']);
    }

    /** @test */
    public function itShouldReturnErrorIfAccountNotFound()
    {
        $this->fakeGuzzleAnAccountNotFoundResponse();

        $accountUuid = 'a673086b-44eb-3850-a26e-f52baca1619f4';

        $response = $this->wallet->account($accountUuid);

        $this->assertTrue($response['error']);
        $this->assertEquals('account not found', $response['message']);
    }

    /** @test */
    public function itShouldReturnAllTransactionsInAPeriodOfDate()
    {
        $this->fakeGuzzleTransactionBetweenAPeriodDate();

        $from = '2020-02-04 13:43:09';
        $to = '2020-03-19 00:00:00';

        $response = $this->wallet->transactionsBetween($from, $to);

        $this->assertIsArray($response);

        $this->assertLessThanInArrat($response, 'created_at', $from);
        $this->assertGreaterThanInArrat($response, 'created_at', $to);
    }

    /** @test */
    public function itShouldReturnErrorIfAllTransactionsInAPeriodOfDateFailed()
    {
        $this->fakeGuzzleFailResponse();

        $from = '2020-02-04 13:43:09';
        $to = '2020-03-19 00:00:00';

        $response = $this->wallet->transactionsBetween($from, $to);

        $this->assertIsArray($response);

        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertTrue($response['error']);
    }

    /** @test */
    public function itShouldSendDepositRequest()
    {
        $this->fakeGuzzleDepositResponse();
        $userId = 1;
        $coinType = 'BTC';
        $amount = 0.001;

        $response = $this->wallet->deposit($userId, $coinType, $amount);

        $this->assertIsArray($response);
        $this->assertSame($response, [
            'user_id' => $userId,
            'coin_type' => $coinType,
            'balance' => $amount,
            'blocked_balance' => 0
        ]);
    }

    /** @test */
    public function itShouldSendWithdrawRequest()
    {
        $this->fakeGuzzleDepositResponse();
        $userId = 1;
        $coinType = 'BTC';
        $amount = 0.001;

        $response = $this->wallet->withdraw($userId, $coinType, $amount);

        $this->assertIsArray($response);
        $this->assertSame($response, [
            'user_id' => $userId,
            'coin_type' => $coinType,
            'balance' => $amount,
            'blocked_balance' => 0
        ]);
    }

    public function fakeGuzzleSuccessResponse()
    {
        $expectedResponseBody = file_get_contents(__DIR__.'/stub/jsonTest.json');
        $this->appendToHandler(200, [], $expectedResponseBody);
    }
    public function fakeGuzzleFailResponse()
    {
        $expectedResponseBody = file_get_contents(__DIR__.'/stub/jsonTest.json');
        $this->appendToHandler(400 , [], $expectedResponseBody);
    }
    public function fakeGuzzleTransactionsResponse()
    {
        $expectedResponseBody = file_get_contents(__DIR__.'/stub/transactions.json');
        $this->appendToHandler(200 , [], $expectedResponseBody);
    }
    public function fakeGuzzleAnAccountResponse()
    {
        $expectedResponseBody = file_get_contents(__DIR__.'/stub/account.json');
        $this->appendToHandler(200 , [], $expectedResponseBody);
    }
    public function fakeGuzzleAnAccountNotFoundResponse()
    {
        $expectedResponseBody = file_get_contents(__DIR__.'/stub/accountNotFound.json');
        $this->appendToHandler(200 , [], $expectedResponseBody);
    }
    public function fakeGuzzleTransactionBetweenAPeriodDate()
    {
        $expectedResponseBody = file_get_contents(__DIR__.'/stub/transactionsBetweenDate.json');
        $this->appendToHandler(200 , [], $expectedResponseBody);
    }
    public function fakeGuzzleFailGraphqlResponse()
    {
        $expectedResponseBody = file_get_contents(__DIR__.'/stub/graphqlError.json');
        $this->appendToHandler(200 , [], $expectedResponseBody);
    }
    public function fakeGuzzleDepositResponse()
    {
        $expectedResponseBody = file_get_contents(__DIR__.'/stub/deposit.json');
        $this->appendToHandler(200 , [], $expectedResponseBody);
    }

    protected function assertLessThanInArrat($array, $key, $actual)
    {
        (collect($array))->map(function ($item) use ($key, $actual){
            $this->assertLessThan($item[$key], $actual);
        });
    }

    protected function assertGreaterThanInArrat($array, $key, $actual)
    {
        (collect($array))->map(function ($item) use ($key, $actual){
            $this->assertGreaterThan($item[$key], $actual);
        });
    }
}
