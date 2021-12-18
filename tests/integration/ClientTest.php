<?php declare(strict_types=1);
 /**
 * Client for Freepaid's SOAP interface.
 *
 * @author    Jacques Marneweck <jacques@powertrip.co.za>
 * @copyright 2013-2021 Jacques Marneweck.  All rights strictly reserved.
 * @license   MIT
 */

namespace Jacques\Freepaid\Tests\Integration;

use Brick\VarExporter\VarExporter;

final class ClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     */
    public function testEmptyContructor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide a url to use to connect to Freepaid.');
        $client = new \Jacques\Freepaid\Client();
        $this->assertNotNull($client);
        $this->assertInstanceOf(\Jacques\Freepaid\Client::class, $client);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     */
    public function testContructorOptionsMissingHostname(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide a url to use to connect to Freepaid.');
        $client = new \Jacques\Freepaid\Client([
            'username' => '12345678',
            'password' => 'password',
        ]);
        $this->assertNotNull($client);
        $this->assertInstanceOf(\Jacques\Freepaid\Client::class, $client);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     */
    public function testContructorOptionsMissingUsername(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide the username to use for requests to Freepaid.');
        $client = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
        ]);
        $this->assertNotNull($client);
        $this->assertInstanceOf(\Jacques\Freepaid\Client::class, $client);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     */
    public function testContructorOptionsMissingPassword(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide the password to use for requests to Freepaid.');
        $client = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '12345678',
        ]);
        $this->assertNotNull($client);
        $this->assertInstanceOf(\Jacques\Freepaid\Client::class, $client);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @covers \Jacques\Freepaid\Client::getBalance
     * @vcr unittest_integration_getbalance
     */
    public function testGetBalance(): void
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);
        $response = $freepaid->getBalance();

        $this->assertInstanceOf(\stdClass::class, $response);
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
    public function testGetLastTransaction(): void
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);
        $response = $freepaid->getLastTransaction('2016120316274783');

        $this->assertInstanceOf(\stdClass::class, $response);
        $this->assertEquals(1, $response->status);
        $this->assertEquals('100', $response->errorcode);
        $this->assertEquals('-', $response->message);
        $this->assertEquals('0', $response->balance);
        $this->assertEquals('-', $response->orderno);
        $this->assertIsArray($response->vouchers);
        $this->assertCount(0, $response->vouchers);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @covers \Jacques\Freepaid\Client::pinbased
     * @vcr unittest_integration_pinbased_single_vodacom_r2
     */
    public function testPinbasedSingleVodacomR2(): void
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);
        $uuid = '87134cfe-b964-11e6-85a3-28cfe91331d9';

        $response = $freepaid->pinbased('vodacom', '2', $uuid);

        $this->assertInstanceOf(\stdClass::class, $response);
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
    public function testGetTransactionSingleVodacomR2(): void
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);

        $response = $freepaid->getTransaction('2016120316274783');

	//echo VarExporter::export($response);

	$expected = (object) [
	    'status' => 1,
	    'errorcode' => '000',
	    'message' => '-',
	    'balance' => 99980.09,
	    'orderno' => '2016120316274783',
	    'vouchers' => [
		(object) [
		    'network' => 'vodacom',
		    'sellvalue' => 2.0,
		    'pin' => '125073790182',
		    'serial' => '524516544',
		    'costprice' => 1.81
		]
	    ]
	];
	self::assertEquals($expected, $response);
    }

    /**
     * @covers \Jacques\Freepaid\Client::__construct
     * @covers \Jacques\Freepaid\Client::getTransactionStatus
     * @vcr unittest_integration_gettransactionstatus_single_vodacom_r2
     */
    public function testGetTransactionStatusSingleVodacomR2(): void
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);

        $response = $freepaid->getTransactionStatus('2016120316274783');

        $this->assertInstanceOf(\stdClass::class, $response);
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
    public function testPinlessSingleVodacomR2(): void
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);
        $uuid = 'd63f2ecc-b96b-11e6-8ef6-28cfe91331d9';

        $response = $freepaid->pinless('vodacom', '27811234567', '2', $uuid);
        $this->assertInstanceOf(\stdClass::class, $response);
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
    public function testGetProducts(): void
    {
        $freepaid = new \Jacques\Freepaid\Client([
            'hostname' => 'ws.dev.freepaid.co.za',
            'username' => '1234567',
            'password' => 'p@ssw0rd',
        ]);
        $response = $freepaid->getProducts();

        $this->assertInstanceOf(\stdClass::class, $response);
        $this->assertEquals(1, $response->status);
        $this->assertEquals('000', $response->errorcode);
        $this->assertEquals('-', $response->message);
        $this->assertEquals('99981.90', $response->balance);
        $this->assertIsArray($response->products);
        $this->assertCount(82, $response->products);

        for ($i = 0; $i < 82; ++$i) {
            $this->assertInstanceOf(\stdClass::class, $response->products[$i]);
            $this->assertIsString($response->products[$i]->description);
            $this->assertIsString($response->products[$i]->network);

            $this->assertNotNull($response->products[$i]->description);
            $this->assertNotNull($response->products[$i]->network);
            $this->assertNotNull($response->products[$i]->sellvalue);
            $this->assertNotNull($response->products[$i]->costprice);
            $this->assertNotEmpty($response->products[$i]->description);
            $this->assertNotEmpty($response->products[$i]->network);

            $this->assertMatchesRegularExpression('!\A(vodacom|pd?-vodacom|mtn|pd?-mtn|cellc|pd?-cellc|branson|telkom|neotel|heita|p-heita|worldcall|worldchat|eskom|bela)\z!', $response->products[$i]->network);
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
