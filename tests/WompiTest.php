<?php

namespace Bancolombia;

use Bancolombia\Wompi;

require __DIR__ . '/env.php';


beforeEach(function () use ($env) {
    $this->config = $env;

    Wompi::initialize($this->config);

    $this->faker = \Faker\Factory::create();

    $this->acceptance_token = Wompi::acceptance_token()->data->presigned_acceptance->acceptance_token;

    $this->fakeData =  [
        "amount_in_cents" => 30300000,
        "currency" => "COP",
        "customer_email" => $this->faker->email(),
        "reference" => $this->faker->email(),
    ];
});

test('Configuración class wompi', function () {

    expect(Wompi::getTokens())->toEqual($this->config);
});


test('Tokens de aceptación', function () {

    expect(is_null($this->acceptance_token))->toBeFalse();
});



test('Tokeniza una tarjeta', function () {

    $token = Wompi::tokenize_card(
        [
            "number" => "4242424242424242", // Número de la tarjeta
            "cvc" => "123", // Código de seguridad de la tarjeta (3 o 4 dígitos según corresponda)
            "exp_month" => "08", // Mes de expiración (string de 2 dígitos)
            "exp_year" => "28", // Año expresado en 2 dígitos
            "card_holder" => "José Pérez" // Nombre del tarjetahabiente
        ]
    );

    expect($token->status)->toEqual('CREATED');

    $paymentCard = Wompi::card(
        $this->acceptance_token,
        $token->data->id,
        2,
        $this->fakeData
    );

    sleep(10);

    expect($paymentCard->data->status)->toEqual('PENDING');
});

test('Botón de Transferencia Bancolombia', function () {

    $bancolombia =  Wompi::bancolombia(
        $this->acceptance_token,
        $this->faker->text(),
        $this->fakeData
    );

    sleep(5);

    $findBancolombia = Wompi::transaction_find_by_id($bancolombia->data->id);

    expect($bancolombia->data->status)->toEqual('PENDING');

    expect($findBancolombia->data->status)->toEqual('APPROVED');
});


test('Nequi', function () {

    $phone = "3991111111";

    $nequi =  Wompi::nequi(
        $this->acceptance_token,
        $phone,
        $this->fakeData
    );

    checkId($nequi->data->id);

    $tokenNequi =  Wompi::tokenize_nequi($phone);

    $subscription = Wompi::subscription_nequi($tokenNequi->data->id);

    checkId($subscription->data->id);
});

test('financial institutions', function () {

    $responses =  Wompi::financial_institutions();

    expect($responses)->toHaveKey('data');

    $responses =  Wompi::pse(
        $this->acceptance_token,
        0,
        'CC',
        '985874589',
        $responses->data[0]->financial_institution_code,
        $this->faker->text(),
        $this->fakeData

    );

    checkId($responses->data->id);
});

test('Pago en efectivo en Corresponsales Bancarios Bancolombia', function () {

    $responses =  Wompi::bancolombia_collect(
        $this->acceptance_token,
        $this->fakeData
    );

    $findBancolombia = Wompi::transaction_find_by_id($responses->data->id);

    checkId($findBancolombia->data->id);
});

test('link de pago', function ()  {

  $link =  Wompi::link(
        [
            "name" => "Pago de arriendo edificio Lombardía - AP 505", // Nombre del link de pago
            "description" => "Arriendo mensual", // Descripción del pago
            "single_use" => false, // `false` en caso de que el link de pago pueda recibir múltiples transacciones APROBADAS o `true` si debe dejar de aceptar transacciones después del primer pago APROBADO
            "collect_shipping" => false // Si deseas que el cliente inserte su información de envío en el checkout, o no
        ]
    );

    checkId($link['response']->data->id);

});

test('check_event', function () {

    list($webHookFake, $webHookBadFake) = webHookFake();

    expect(Wompi::check_event($webHookFake))->toEqual(true);

    expect(Wompi::check_event($webHookBadFake))->toEqual(false);

    expect(Wompi::check_event([]))->toEqual(false);

});

function webHookFake()
{

    return  [
        [
            "event" => "transaction.updated",
            "data" => [
                "transaction" => [
                    "id" => "114167-1654990057-62612",
                    "created_at" => "2022-06-11T23:27:37.623Z",
                    "finalized_at" => "2022-06-11T23:27:37.000Z",
                    "amount_in_cents" => 30300000,
                    "reference" => "ybrekke@gmail.com",
                    "customer_email" => "finn.schulist@sanford.com",
                    "currency" => "COP",
                    "payment_method_type" => "BANCOLOMBIA_TRANSFER",
                    "payment_method" => [
                        "type" => "BANCOLOMBIA_TRANSFER",
                        "extra" => [
                            "async_payment_url" => "https://sandbox.wompi.co/v1/payment_methods/redirect/bancolombia_transfer?transferCode=BkjOZiceeCYwi8oY-approved",
                            "external_identifier" => "BkjOZiceeCYwi8oY-approved"
                        ],
                        "user_type" => "PERSON",
                        "sandbox_status" => "APPROVED",
                        "payment_description" => "Eaque iusto exercitationem maiores aspernatur. Et et sint doloribus ullam veniam molestiae dicta ipsa. Error velit qui voluptas in."
                    ],
                    "status" => "APPROVED",
                    "status_message" => null,
                    "shipping_address" => null,
                    "redirect_url" => null,
                    "payment_source_id" => null,
                    "payment_link_id" => null,
                    "customer_data" => null,
                    "billing_data" => null
                ]
            ],
            "sent_at" => "2022-06-11T23:27:37.810Z",
            "timestamp" => 1654990057,
            "signature" => [
                "checksum" => "aa2512232048f87233123110a742fb8dab5d99e1bd7d6f9572751c35210f0542",
                "properties" => [
                    "transaction.id",
                    "transaction.status",
                    "transaction.amount_in_cents"
                ]
            ],
            "environment" => "test"
        ],
        [
            "event" => "transaction.updated",
            "data" => [
                "transaction" => [
                    "id" => "114167-1654990057-62612",
                    "created_at" => "2022-06-11T23:27:37.623Z",
                    "finalized_at" => "2022-06-11T23:27:37.000Z",
                    "amount_in_cents" => 30300000,
                    "reference" => "ybrekke@gmail.com",
                    "customer_email" => "finn.schulist@sanford.com",
                    "currency" => "COP",
                    "payment_method_type" => "BANCOLOMBIA_TRANSFER",
                    "payment_method" => [
                        "type" => "BANCOLOMBIA_TRANSFER",
                        "extra" => [
                            "async_payment_url" => "https://sandbox.wompi.co/v1/payment_methods/redirect/bancolombia_transfer?transferCode=BkjOZiceeCYwi8oY-approved",
                            "external_identifier" => "BkjOZiceeCYwi8oY-approved"
                        ],
                        "user_type" => "PERSON",
                        "sandbox_status" => "APPROVED",
                        "payment_description" => "Eaque iusto exercitationem maiores aspernatur. Et et sint doloribus ullam veniam molestiae dicta ipsa. Error velit qui voluptas in."
                    ],
                    "status" => "APPROVED",
                    "status_message" => null,
                    "shipping_address" => null,
                    "redirect_url" => null,
                    "payment_source_id" => null,
                    "payment_link_id" => null,
                    "customer_data" => null,
                    "billing_data" => null
                ]
            ],
            "sent_at" => "2022-06-11T23:27:37.810Z",
            "timestamp" => 1658991057,
            "signature" => [
                "checksum" => "aa2512232048f87233123110a744fb8dab5d99e1bd7d6f9572751c35210f0542",
                "properties" => [
                    "transaction.id",
                    "transaction.status",
                    "transaction.amount_in_cents"
                ]
            ],
            "environment" => "test"
        ]
    ];

}

