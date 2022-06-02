<?php

use Bancolombia\Wompi;

require __DIR__ . '/env.php';


beforeEach(function () use ($env) {
    $this->config = $env;

    Wompi::setTokens($this->config);

    $this->faker = \Faker\Factory::create();

    $this->acceptance_token = Wompi::acceptanceToken()->data->presigned_acceptance->acceptance_token;
});

test('Configuración class wompi', function () {

    expect(Wompi::getTokens())->toEqual($this->config);
});


test('Tokens de aceptación', function () {

    expect(is_null($this->acceptance_token))->toBeFalse();
});



test('Tokeniza una tarjeta', function () {

    $token = Wompi::tokenizeCard(
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
        [
            "amount_in_cents" => 80000000,
            "currency" => "COP",
            "customer_email" => $this->faker->email(),
            "reference" => $this->faker->email(),
        ]
    );

    expect($paymentCard->data->status)->toEqual('PENDING');
});

test('Botón de Transferencia Bancolombia', function () {

    $bancolombia =  Wompi::bancolombia(
        $this->acceptance_token,
        $this->faker->text(),
        [
            "amount_in_cents" => 10000000,
            "currency" => "COP",
            "customer_email" => $this->faker->email(),
            "reference" => $this->faker->email(),
        ]
    );

    $findBancolombia = Wompi::findTransaction($bancolombia->data->id);

    expect($bancolombia->data->status)->toEqual('PENDING');

    expect($findBancolombia->data->status)->toEqual('PENDING');
});


test('Nequi', function () {

    $nequi =  Wompi::nequi(
        $this->acceptance_token,
        "3107654321",
        [
            "amount_in_cents" => 10000000,
            "currency" => "COP",
            "customer_email" => $this->faker->email(),
            "reference" => $this->faker->email(),
        ]
    );

    expect($nequi->data->status)->toEqual('PENDING');
});