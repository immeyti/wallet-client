<?php


namespace Immeyti\WalletClient\Tests;

use Immeyti\WalletClient\Tests\FakeGuzzleClientResponses;
use Immeyti\WalletClient\Wallet;

class WalletTest extends \Tests\TestCase
{
    use FakeGuzzleClientResponses;


    /** @test */
    public function itShouldReturnAnArrayIfRequestSeccess()
    {
        $this->fakeGuzzleSuccessResponse();

        $wallet = new Wallet();

        $response = $wallet->request('https://dog.ceo/api/breeds/list/all', 'GET');

        $this->assertTrue(is_array($response));
    }

    /** @test */
    public function itShouldReturnAnExceptionMessageIfRequestFailed()
    {
        $this->fakeGuzzleFailResponse();

        $wallet = new Wallet();
        $response = $wallet->request('https://dog.ceo/api/breeds/list/sdf', 'POST');

        $this->assertTrue(is_array($response));
        $this->assertTrue(key_exists('message', $response));
    }

    /** @test */
    public function itShouldReturnAnArrayOfAllTransactions()
    {

        $wallet = new Wallet();
        $allTransactions = $wallet->allTransactions();

        $this->assertTrue(count($allTransactions) > 0);
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
        $this->appendToHandler(400 , [], $expectedResponseBody);
    }
}
