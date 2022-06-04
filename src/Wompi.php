<?php

namespace Bancolombia;


class Wompi
{

    public static $resClient;

    /**
     * wompi constructor.
     */
    public static function initialize($tokens)
    {
        static::$resClient = new RestClient($tokens);
    }


    /**
     *
     * @return array
     */
    public static function getTokens()
    {

        return static::$resClient->tokens;
    }


    public static function acceptance_token()
    {
        $token = static::$resClient->getPublicKey();
        return  static::$resClient->get("/merchants/{$token}");
    }

    /**
     * Tokeniza una tarjeta
     * @param array $data
     * @link https://docs.wompi.co/docs/en/fuentes-de-pago
     */
    public static function tokenize_card($data)
    {

        return  static::$resClient->post("/tokens/cards", $data, static::$resClient->getPublicKey());
    }

    /**
     * Tokeniza una cuenta nequi
     * @param string $phone_number
     * @link https://docs.wompi.co/docs/en/fuentes-de-pago
     */
    public static function tokenize_nequi($phone_number)
    {
        return  static::$resClient->post(
            "/tokens/nequi",
            [
                'phone_number' => $phone_number
            ],
            static::$resClient->getPublicKey()
        );
    }

    /**
     * chequear el estado de la suscripción en nequi
     * @param string $tokenizeNequiId
     * @link https://docs.wompi.co/docs/en/fuentes-de-pago
     */
    public static function subscription_nequi($tokenizeNequiId)
    {
        return  static::$resClient->get(
            "/tokens/nequi/{$tokenizeNequiId}",
            static::$resClient->getPublicKey()
        );
    }


    /**
     *  lista de instituciones financieras
     * @link https://app.swaggerhub.com/apis-docs/waybox/wompi/1.0.0-oas3#/Comercios/get_merchants__merchantPublicKey_
     */
    public static function financial_institutions()
    {
        $token = static::$resClient->getPublicKey();
        return  static::$resClient->get("/pse/financial_institutions", $token);
    }

    /**
     * Crea una fuente de pago
     * @param string $customer_email
     * @param string $token
     * @param string $acceptance_token
     * @link https://docs.wompi.co/docs/en/metodos-de-pago
     */
    public static function payment_sources(
        $tokenizeId,
        $customer_email,
        $acceptance_token
    ) {
        return  static::$resClient->post(
            "/payment_sources",
            [
                "type" => str_contains($tokenizeId, 'nequi') ? 'NEQUI' : 'CARD',
                "token" => $tokenizeId,
                "customer_email" => $customer_email,
                "acceptance_token" => $acceptance_token
            ]
        );
    }

    /**
     * Realiza transacción con targeta de credito
     * @param array $data
     * @param string $token
     * @param string $acceptanceToken
     * @param int $installments
     * @link https://docs.wompi.co/docs/en/metodos-de-pago
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
     * @link https://docs.wompi.co/docs/en/metodos-de-pago
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
     * Pago en efectivo en corresponsales bancarios bancolombia
     * @param array $data
     * @param string $acceptanceToken
     * @link https://docs.wompi.co/docs/en/metodos-de-pago
     */
    public static function bancolombia_collect($acceptanceToken, $data)
    {

        return static::transaction(array_merge(
            static::paymentMethod(
                $acceptanceToken,
                [
                    "type" => "BANCOLOMBIA_COLLECT",
                    "sandbox_status" => "APPROVED"
                ]
            ),
            $data
        ));
    }


    /**
     * Realiza transacción con PSE
     * @param array $data
     * @param int $user_type tipo de persona, natural (0) o jurídica (1)
     * @param  string $user_legal_id_type tipo de documento, CC o NIT
     * @param string $user_legal_id número de documento
     * @param string $description nombre de lo que se está pagando. Máximo 30 caracteres
     * @param string $financial_institution_code código (`code`) de la institución financiera
     * @param string $acceptanceToken
     * @link https://docs.wompi.co/docs/en/metodos-de-pago
     */
    public static function pse(
        $acceptanceToken,
        $user_type,
        $user_legal_id_type,
        $user_legal_id,
        $financial_institution_code,
        $description,
        $data
    ) {

        return static::transaction(array_merge(
            static::paymentMethod(
                $acceptanceToken,
                [
                    "type" => "PSE",
                    "user_type" => $user_type, // Tipo de persona, natural (0) o jurídica (1)
                    "user_legal_id_type" => $user_legal_id_type, // Tipo de documento, CC o NIT
                    "user_legal_id" => $user_legal_id, // Número de documento
                    "financial_institution_code" => $financial_institution_code, // Código (`code`) de la institución financiera
                    "payment_description" => $description // Nombre de lo que se está pagando. Máximo 30 caracteres

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
     * @link https://docs.wompi.co/docs/en/metodos-de-pago
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
     * @link https://docs.wompi.co/docs/en/metodos-de-pago
     */
    public static function transaction($data)
    {

        return  static::$resClient->post("/transactions", $data);
    }

    /**
     * Realiza la transacción
     * @param string $reference
     * @link https://app.swaggerhub.com/apis-docs/waybox/wompi/1.0.0-oas3#/
     */
    public static function find_transaction($reference)
    {
        return  static::$resClient->get("/transactions?reference={$reference}");
    }


    /**
     * cancelar la transacción
     * @param string $transaction_id
     * @link https://app.swaggerhub.com/apis-docs/waybox/wompi/1.0.0-oas3#/
     */
    public static function cancell_transaction($transaction_id)
    {

        return  static::$resClient->post("/transactions/{$transaction_id}/void");
    }

    /**
     * Creación del link de pago
     * @param array $data
     * @link https://docs.wompi.co/docs/en/links-de-pago
     */
    public static function link($data)
    {
        $result = static::$resClient->post("/payment_links", $data);

        return [
            'response' => $result,
            'link' => "https://checkout.wompi.co/l/{$result->data->id}"
        ];
    }

    /**
     * consultar  transacción
     * @param array $data
     * @link https://docs.wompi.co/docs/en/metodos-de-pago
     */
    public static function transaction_find_by_id($transaction_id)
    {

        return  static::$resClient->get("/transactions/{$transaction_id}");
    }

    /**
     * Verifica la autenticidad de un evento
     * @param mixed $response
     * @link https://docs.wompi.co/docs/en/eventos
     * @return bool
     */
    public static function check_event($response)
    {
        $data = $response['data'];
        $transaction = $data['transaction'];
        $privateEventKey = static::$resClient->getPrivateEventKey();
        $token = "{$transaction['id']}{$transaction['status']}{$transaction['amount_in_cents']}{$response['timestamp']}{$privateEventKey}";
        $checksum = hash('sha256', $token);

        return $checksum === $response['signature']['checksum'];
    }
}
