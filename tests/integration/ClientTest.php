<?php declare(strict_types=1);
 /**
 * Client for Freepaid's SOAP interface.
 *
 * @author    Jacques Marneweck <jacques@powertrip.co.za>
 * @copyright 2013-2019 Jacques Marneweck.  All rights strictly reserved.
 * @license   MIT
 */

namespace Jacques\Freepaid\Tests\Integration;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please provide a url to use to connect to Freepaid.
     */
    public function testEmptyContructor()
    {
        $client = new \Jacques\Freepaid\Client();
        $this->assertNotNull($client);
        $this->assertInstanceOf('\Jacques\Freepaid\Client', $client);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please provide a url to use to connect to Freepaid.
     */
    public function testContructorOptionsMissingHostname()
    {
        $client = new \Jacques\Freepaid\Client([
            'username' => '12345678',
            'password' => 'password',
        ]);
        $this->assertNotNull($client);
        $this->assertInstanceOf('\Jacques\Freepaid\Client', $client);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please provide the username to use for requests to Freepaid.
     */
    public function testContructorOptionsMissingUsername()
    {
        $client = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
        ]);
        $this->assertNotNull($client);
        $this->assertInstanceOf('\Jacques\Freepaid\Client', $client);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please provide the password to use for requests to Freepaid.
     */
    public function testContructorOptionsMissingPassword()
    {
        $client = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '12345678',
        ]);
        $this->assertNotNull($client);
        $this->assertInstanceOf('\Jacques\Freepaid\Client', $client);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @covers \Jacques\Freepaid\Client::getBalance
     * @vcr unittest_integration_getbalance
     */
    public function testGetBalance()
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);
        $response = $freepaid->getBalance();

        $this->assertInstanceOf('\stdClass', $response);
        $this->assertEquals(1, $response->status);
        $this->assertEquals('000', $response->errorcode);
        $this->assertEquals('-', $response->message);
        $this->assertEquals('99981.90', $response->balance);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @covers \Jacques\Freepaid\Client::getLastTransaction
     * @vcr unittest_integration_getlasttansaction
     */
    public function testGetLastTransaction()
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);
        $response = $freepaid->getLastTransaction('2016120316274783');

        $this->assertInstanceOf('\stdClass', $response);
        $this->assertEquals(1, $response->status);
        $this->assertEquals('100', $response->errorcode);
        $this->assertEquals('-', $response->message);
        $this->assertEquals('0', $response->balance);
        $this->assertEquals('-', $response->orderno);
        $this->assertInternalType('array', $response->vouchers);
        $this->assertCount(0, $response->vouchers);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @covers \Jacques\Freepaid\Client::pinbased
     * @vcr unittest_integration_pinbased_single_vodacom_r2
     */
    public function testPinbasedSingleVodacomR2()
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);
        $uuid = '87134cfe-b964-11e6-85a3-28cfe91331d9';

        $response = $freepaid->pinbased('vodacom', '2', $uuid);

        $this->assertInstanceOf('\stdClass', $response);
        $this->assertEquals(1, $response->status);
        $this->assertEquals('000', $response->errorcode);
        $this->assertEquals('success', $response->message);
        $this->assertEquals('99980.09', $response->balance);
        $this->assertEquals('2016120316274783', $response->orderno);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @covers \Jacques\Freepaid\Client::getTransaction
     * @vcr unittest_integration_gettransaction_single_vodacom_r2
     */
    public function testGetTransactionSingleVodacomR2()
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);

        $response = $freepaid->getTransaction('2016120316274783');

        $this->assertInstanceOf('\stdClass', $response);
        $this->assertEquals(1, $response->status);
        $this->assertEquals('000', $response->errorcode);
        $this->assertEquals('-', $response->message);
        $this->assertEquals('99980.09', $response->balance);
        $this->assertEquals('2016120316274783', $response->orderno);
        $this->assertInternalType('array', $response->vouchers);
        $this->assertCount(1, $response->vouchers);

        for ($i = 0; $i < 1; $i++) {
            $this->assertInstanceOf('\stdClass', $response->vouchers[$i]);
            $this->assertInternalType('string', $response->vouchers[$i]->network);
            $this->assertInternalType('double', $response->vouchers[$i]->sellvalue);
            $this->assertInternalType('string', $response->vouchers[$i]->pin);
            $this->assertInternalType('string', $response->vouchers[$i]->serial);
            $this->assertInternalType('double', $response->vouchers[$i]->costprice);
        }

        /*
         * 0
         */
        $this->assertEquals('vodacom', $response->vouchers['0']->network);
        $this->assertEquals(2, $response->vouchers['0']->sellvalue);
        $this->assertEquals('125073790182', $response->vouchers['0']->pin);
        $this->assertEquals('524516544', $response->vouchers['0']->serial);
        $this->assertEquals(1.81, $response->vouchers['0']->costprice);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @covers \Jacques\Freepaid\Client::getTransactionStatus
     * @vcr unittest_integration_gettransactionstatus_single_vodacom_r2
     */
    public function testGetTransactionStatusSingleVodacomR2()
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);

        $response = $freepaid->getTransactionStatus('2016120316274783');

        $this->assertInstanceOf('\stdClass', $response);
        $this->assertEquals(0, $response->status);
        $this->assertEquals('112', $response->errorcode);
        $this->assertEquals('orderno [2016120316274783] not found?', $response->message);
        $this->assertEquals('99980.09', $response->balance);
        $this->assertEquals('-', $response->orderno);
        $this->assertEquals(0, $response->costprice);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @covers \Jacques\Freepaid\Client::pinless
     * @vcr unittest_integration_pinless_single_vodacom_r2
     */
    public function testPinlessSingleVodacomR2()
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);
        $uuid = 'd63f2ecc-b96b-11e6-8ef6-28cfe91331d9';

        $response = $freepaid->pinless('vodacom', '27811234567', '2', $uuid);
        $this->assertInstanceOf('\stdClass', $response);
        $this->assertEquals(1, $response->status);
        $this->assertEquals('000', $response->errorcode);
        $this->assertEquals('success', $response->message);
        $this->assertEquals('99978.28', $response->balance);
        $this->assertEquals('2016120317224107', $response->orderno);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @covers \Jacques\Freepaid\Client::getProducts
     * @vcr unittest_integration_getproducts
     */
    public function testGetProducts()
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);
        $response = $freepaid->getProducts();

        $this->assertInstanceOf('\stdClass', $response);
        $this->assertEquals(1, $response->status);
        $this->assertEquals('000', $response->errorcode);
        $this->assertEquals('-', $response->message);
        $this->assertEquals('99981.90', $response->balance);
        $this->assertInternalType('array', $response->products);
        $this->assertCount(82, $response->products);

        for ($i = 0; $i < 82; $i++) {
            $this->assertInstanceOf('\stdClass', $response->products[$i]);
            $this->assertInternalType('string', $response->products[$i]->description);
            $this->assertInternalType('string', $response->products[$i]->network);

            $this->assertNotNull($response->products[$i]->description);
            $this->assertNotNull($response->products[$i]->network);
            $this->assertNotNull($response->products[$i]->sellvalue);
            $this->assertNotNull($response->products[$i]->costprice);
            $this->assertNotEmpty($response->products[$i]->description);
            $this->assertNotEmpty($response->products[$i]->network);

            $this->assertRegexp('!\A(vodacom|pd?-vodacom|mtn|pd?-mtn|cellc|pd?-cellc|branson|telkom|neotel|heita|p-heita|worldcall|worldchat|eskom|bela)\z!', $response->products[$i]->network);
        }

        /*
         * 0
         */
        $this->assertEquals('Bela', $response->products['0']->description);
        $this->assertEquals('bela', $response->products['0']->network);
        $this->assertEquals(10, $response->products['0']->sellvalue);
        $this->assertEquals(9.05, $response->products['0']->costprice);
    }
}
