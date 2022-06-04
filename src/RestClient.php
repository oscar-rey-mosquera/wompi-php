<?php

namespace Bancolombia;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class RestClient
{

    public $client;

    public $tokens;

    public $api_version = 'v1';

    public function __construct($tokens)
    {
       $this->tokens = $tokens;
        $this->init();
    }


    /** 
     * Inicializa el cliente http
     * @return void
     */
    public function init()
    {
        $this->client =  new Client([
            'base_uri' => $this->getBaseUrl()
        ]);
    }

   
    /**
     * Obtiene la url base de la api
     * @return string
     */
    public function getBaseUrl()
    {
        $url = 'https://sandbox.wompi.co';

        if(str_contains($this->getPrivateKey(), 'prod') && str_contains($this->getPublicKey(), 'prod') ) {

            $url = 'https://production.wompi.co';
        }

        return $url;
    }


    /**
     * @param string $url
     * @param string $token
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
     * @param array $data
     * @param string $token
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
     * @param array $data
     * @param string $token
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
     * @param string $token
     * @param array $data
     */
    public function delete($url, $data = [], $token = null)
    {

        return $this->handlerError(function () use ($url, $token, $data) {

            $request = $this->client->delete("{$this->api_version}{$url}", $this->getBody($data, $token));

            return $request;
        });
    }

    /**
     * private key configurada
     */
    public function getPrivateKey()
    {

        return $this->tokens['private_key'];
    }

     /**
     * private event key configurada
     */
    public function getPrivateEventKey()
    {

        return $this->tokens['private_event_key'];
    }



    /**
     * public key configurada
     */
    public function getPublicKey()
    {

        return $this->tokens['public_key'];
    }

    /**
     * Preparación del cuerpo de la petición http
     */
    public function getBody($data = [], $token = null)
    {
        return array_merge(
            [
                'body' => json_encode($data),
            ],
            $this->getHeader($token)
        );
    }

    /**
     * Generación de cabeceras de la petición http
     */
    public function getHeader($token)
    {
        $token = $token ?? $this->getPrivateKey();

        return [
            'headers' => [
                'authorization' => "Bearer {$token}"
            ]
        ];
    }

    /**
     * Enganchado de errores
     */
    public function handlerError($callback)
    {
        try {
            return json_decode($callback()->getBody()->getContents());
        } catch (ClientException $e) {
            return json_decode($e->getResponse()->getBody()->getContents());
        }
    }
}
