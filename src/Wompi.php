<?php

namespace Bancolombia;

class Wompi
{

    public static $resClient;

    /**
     * wompi constructor.
     */
    public static function initialize()
    {
        static::$resClient = new RestClient();
    }


    /**
     * Agregar tonkens
     * @param array $tokens
     */
    public static function setTokens($tokens)
    {
        self::initialize();
        self::$resClient->tokens = $tokens;
    }

    /**
     * 
     * @return array 
     */
    public static function getTokens()
    {

        return static::$resClient->tokens;
    }


    public static function acceptanceToken()
    {
        $token = static::getPublicKey();
        return  static::$resClient->get("/merchants/{$token}");
    }

    /**
     * Tokeniza una tarjeta
     * @param array $data
     */
    public static function tokenizeCard($data)
    {

        return  static::$resClient->post("/tokens/cards", $data, static::getPublicKey());
    }


    /**
     *  lista de instituciones financieras 
     */
    public static function financialInstitutions()
    {

        return  static::$resClient->get("/pse/financial_institutions");
    }


    /**
     * Realiza transacción con targeta de credito
     * @param array $data
     * @param string $token
     * @param string $acceptanceToken
     * @param int $installments
     */
    public static function card($acceptanceToken, $token, $installments, $data)
    {

        return static::transaction(array_merge(
            static::paymentMethod(
                $acceptanceToken,
                [
                    "type" => "CARD",
                    "installments" => $installments, // Número de cuotas
                    "token" => $token
                ]
            ),
            $data
        ));
    }


    /**
     * Realiza transacción con nequi
     * @param array $data
     * @param string $phoneNumber
     * @param string $acceptanceToken
     */
    public static function nequi($acceptanceToken, $phoneNumber, $data)
    {

        return static::transaction(array_merge(
            static::paymentMethod(
                $acceptanceToken,
                [
                    "type" => "NEQUI",
                    "phone_number" => $phoneNumber
                ]
            ),
            $data
        ));
    }


    /**
     * Botón de Transferencia Bancolombia
     * @param array $data
     * @param string $token
     * @param string $acceptanceToken
     * @param int $installments
     * @todo Quitar el sandbox_status
     */
    public static function bancolombia($acceptanceToken, $description, $data)
    {

        return static::transaction(array_merge(
            static::paymentMethod(
                $acceptanceToken,
                [
                    "type" => "BANCOLOMBIA_TRANSFER",
                    "user_type" =>  "PERSON", // Tipo de persona
                    "payment_description" => $description,
                    "sandbox_status" => "APPROVED"
                ]
            ),
            $data
        ));
    }

    private static function paymentMethod($acceptanceToken, $data)
    {

        return [
            'acceptance_token' => $acceptanceToken,
            'payment_method' => $data
        ];
    }

    /**
     * Realiza la transacción
     * @param array $data
     */
    public static function transaction($data)
    {

        return  static::$resClient->post("/transactions", $data);
    }

    /**
     * consultar  transacción
     * @param array $data
     */
    public static function findTransaction($transaction_id)
    {

        return  static::$resClient->get("/transactions/{$transaction_id}");
    }


    public static function getPublicKey()
    {

        return self::$resClient->tokens['public_key'];
    }
}
