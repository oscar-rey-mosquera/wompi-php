<?php
namespace Bancolombia;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;

class RestClient {

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
    public function init() {

        $this->client =  new Client([
            'base_uri' => 'https://sandbox.wompi.co'
        ]);

    }


    /**
     * @param string $url
     */
    public function get($url) {

        return $this->handlerError(function () use ($url) {

            $request = $this->client->get("{$this->api_version}{$url}");

            return $request;
        });
    }


    /**
     * @param string $url
     */
    public function post($url, $data = [], $token = null) {

        return $this->handlerError(function () use ($url, $token, $data) {

            $request = $this->client->post("{$this->api_version}{$url}", $this->getBody($data, $token));

            return $request;
        });
    }

        /**
     * @param string $url
     */
    public function put($url, $data = [], $token = null) {

        return $this->handlerError(function () use ($url, $token, $data) {

            $request = $this->client->put("{$this->api_version}{$url}", $this->getBody($data, $token));

            return $request;
        });
    }


        /**
     * @param string $url
     */
    public function delete($url, $data = [], $token = null) {

        return $this->handlerError(function () use ($url, $token, $data) {

            $request = $this->client->delete("{$this->api_version}{$url}", $this->getBody($data,$token));

            return $request;
        });
    }


    public function getBody($data = [], $token = null) {
     
        $token = $token ?? $this->tokens['private_key'];
        return [
            'body' => json_encode($data),
            'headers' => [
              'authorization' => "Bearer {$token}"
            ]
        ];

    }


    public function handlerError($callback) {

        try {
           return json_decode($callback()->getBody()->getContents());
        } catch (ClientException $e) {
           return json_decode($e->getResponse()->getBody()->getContents());
        }
    }



}