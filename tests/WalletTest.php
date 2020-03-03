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
        $wallet = new Wallet();

        $response = $wallet->request('https://dog.ceo/api/breeds/list/test', 'GET');

        $this->assertTrue(is_array($response));
        $this->assertTrue(key_exists('message', $response));
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
}
