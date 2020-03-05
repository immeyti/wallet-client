<?php


namespace Immeyti\WalletClient\Tests;

use Immeyti\WalletClient\Tests\FakeGuzzleClientResponses;
use Immeyti\WalletClient\Wallet;

class WalletTest extends \Tests\TestCase
{
    use FakeGuzzleClientResponses;

    public Wallet $wallet;

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
    public function itShouldReturnErrorMessageWhenFailedAllTransactiosGraphqlRequest()
    {
        $this->fakeGuzzleFailGraphqlResponse();

        $wallet = new Wallet();
        $allTransactions = $wallet->allTransactions();

        $this->assertTrue(is_array($allTransactions));
        $this->assertSame(
            ['message' => "Cannot query field \"idsdf\" on type \"Transaction\"."],
            $allTransactions);
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

    public function fakeGuzzleFailGraphqlResponse()
    {
        $expectedResponseBody = file_get_contents(__DIR__.'/stub/graphqlError.json');
        $this->appendToHandler(200 , [], $expectedResponseBody);
    }
}
