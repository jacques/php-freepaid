<?php
/**
 * Client for Freepaid's SOAP interface.
 *
 * @author    Jacques Marneweck <jacques@powertrip.co.za>
 * @copyright 2013-2015 Jacques Marneweck.  All rights strictly reserved.
 * @license   MIT
 */
namespace Jacques\Freepaid;

class Client
{
    /**
     * @var \SoapClient|null
     */
    private $soapclient = null;

    /**
     * @var array
     */
    private $options = [
        'scheme'   => 'http',
        'hostname' => 'ws.dev.freepaid.co.za',
        'port'     => '80',
    ];

    /**
     * Constructor.
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($options = [])
    {
        if (!array_key_exists('hostname', $options)) {
            throw new \InvalidArgumentException('Please provide a url to use to connect to Freepaid.');
        }

        if (!array_key_exists('username', $options)) {
            throw new \InvalidArgumentException('Please provide the username to use for requests to Freepaid.');
        }

        if (!array_key_exists('password', $options)) {
            throw new \InvalidArgumentException('Please provide the password to use for requests to Freepaid.');
        }

        $this->options = array_merge(
            $this->options,
            $options
        );

        $this->soapclient = new \SoapClient(
            sprintf(
                '%s://%s%s/airtimeplus/?wsdl',
                $this->options['scheme'],
                $this->options['hostname'],
                ($this->options['port'] != 80) ? $this->options['port'] : ''
            ),
            [
                'encoding'   => 'US-ASCII',
                'trace'      => 1,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ]
        );
    }

    /**
     * Places and order for pinbased virtual vouchers.
     *
     * @param string $network
     * @param int    $amount
     * @param string $client_ref
     * @param int    $quantity
     *
     * @return stdClass
     */
    public function pinbased($network, $amount, $client_ref, $quantity = 1)
    {
        $request = [
            'user'      => $this->options['username'],
            'pass'      => $this->options['password'],
            'refno'     => $client_ref,
            'network'   => $network,
            'sellvalue' => $amount,
            'count'     => $quantity,
            'extra'     => '',
        ];
        $reply = $this->soapclient->placeOrder($request);

        return $reply;
    }

    /**
     * Places an order for pinless airtime to be delivered to the requested
     * mobile phone number.
     *
     * @param string $network
     * @param string $msisdn
     * @param string $amount
     * @param string $client_ref
     *
     * @return \stdClass
     */
    public function pinless($network, $msisdn, $amount, $client_ref)
    {
        $request = [
            'user'      => $this->options['username'],
            'pass'      => $this->options['password'],
            'refno'     => $client_ref,
            'network'   => $network,
            'sellvalue' => $amount,
            'count'     => 1,
            'extra'     => $msisdn,
        ];
        $reply = $this->soapclient->placeOrder($request);

        return $reply;
    }

    public function getBalance()
    {
        $request = [
            'user' => $this->options['username'],
            'pass' => $this->options['password'],
        ];
        $reply = $this->soapclient->fetchBalance($request);

        return $reply;
    }

    public function getLastTransaction($orderno)
    {
        $request = [
            'user' => $this->options['username'],
            'pass' => $this->options['password'],
            'last' => $orderno,
        ];
        $reply = $this->soapclient->fetchOrderLatest($request);

        return $reply;
    }

    public function getProducts()
    {
        $request = [
            'user' => $this->options['username'],
            'pass' => $this->options['password'],
        ];
        $reply = $this->soapclient->fetchProducts($request);

        return $reply;
    }

    public function getTransaction($orderno)
    {
        $request = [
            'user'    => $this->options['username'],
            'pass'    => $this->options['password'],
            'orderno' => $orderno,
        ];
        $reply = $this->soapclient->fetchOrder($request);

        return $reply;
    }

    public function getTransactionStatus($orderno)
    {
        $request = [
            'user'    => $this->options['username'],
            'pass'    => $this->options['password'],
            'orderno' => $orderno,
        ];
        $reply = $this->soapclient->queryOrder($request);

        return $reply;
    }
}
