<?php

namespace Bancolombia;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;

class RestClient
{

    public $client;

    public $tokens;

    public $api_version = 'v1';

    public function __construct()
    {

        $this->init();
    }


    /** 
     * Inicializa el cliente http
     * @return void
     */
    public function init()
    {

        $this->client =  new Client([
            'base_uri' => 'https://sandbox.wompi.co'
        ]);
    }


    /**
     * @param string $url
     */
    public function get($url, $token = null)
    {

        return $this->handlerError(function () use ($url, $token) {

            $request = $this->client->get("{$this->api_version}{$url}", $this->getHeader($token));

            return $request;
        });
    }


    /**
     * @param string $url
     */
    public function post($url, $data = [], $token = null)
    {

        return $this->handlerError(function () use ($url, $token, $data) {

            $body = $this->getHeader($token);

            if (count($data) > 0) {
                $body = $this->getBody($data, $token);
            }

            $request = $this->client->post(
                "{$this->api_version}{$url}",
                $body
            );

            return $request;
        });
    }

    /**
     * @param string $url
     */
    public function put($url, $data = [], $token = null)
    {

        return $this->handlerError(function () use ($url, $token, $data) {

            $request = $this->client->put("{$this->api_version}{$url}", $this->getBody($data, $token));

            return $request;
        });
    }


    /**
     * @param string $url
     */
    public function delete($url, $data = [], $token = null)
    {

        return $this->handlerError(function () use ($url, $token, $data) {

            $request = $this->client->delete("{$this->api_version}{$url}", $this->getBody($data, $token));

            return $request;
        });
    }

    public function getPrivateKey()
    {

        return $this->tokens['private_key'];
    }


    public function getPublicKey()
    {

        return $this->tokens['public_key'];
    }

    public function getBody($data = [], $token = null)
    {
        return array_merge(
            [
                'body' => json_encode($data),
            ],
            $this->getHeader($token)
        );
    }

    public function getHeader($token)
    {
        $token = $token ?? $this->getPrivateKey();

        return [
            'headers' => [
                'authorization' => "Bearer {$token}"
            ]
        ];
    }


    public function handlerError($callback)
    {
        try {
            return json_decode($callback()->getBody()->getContents());
        } catch (ClientException $e) {
            return json_decode($e->getResponse()->getBody()->getContents());
        }
    }
}
