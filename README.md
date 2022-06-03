<p align="center">
  <img src="https://comunidad.apphive.io/uploads/default/original/2X/4/4e6b2a93f2962dcf06f8683c105cfdd64d3d18b3.png" alt="Logo Laravel Cashier Stripe" width="200px">
</p>

<p align="center">
<a href="https://packagist.org/packages/bancolombia-dev/wompi-php"><img src="https://img.shields.io/packagist/dt/bancolombia-dev/wompi-php" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/bancolombia-dev/wompi-php"><img src="https://img.shields.io/packagist/v/bancolombia-dev/wompi-php" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/bancolombia-dev/wompi-php"><img src="https://img.shields.io/packagist/l/bancolombia-dev/wompi-php" alt="License"></a>
</p>


## Introducción
Este es un paquete de la api de [wompi](https://wompi.co/) que pertenece al grupo Bancolombia para php.

## 💻 Instalación 

Para instalar utiliza [composer](https://getcomposer.org/).

```.bash
composer require bancolombia-dev/wompi-php
```
## 🔧 Uso del paquete
Para iniciar a utilizar el paquete necesitas tu llave pública y llave privada de tu [cuenta wompi](https://comercios.wompi.co/developers).
```php
  /**
   * Autocarga de clases  
   */
require_once 'vendor/autoload.php';

use Bancolombia\Wompi;

Wompi::setTokens([
    'public_key' => '',
    'private_key' => ''
  ]);
  
/**
* Retorna los tokens configurados
*/
Wompi::getTokens();

/**
* token de aceptación
* @link https://docs.wompi.co/docs/en/tokens-de-aceptacion
*/
Wompi::acceptance_token();

/**
* Métodos de pago
* @link https://docs.wompi.co/docs/en/metodos-de-pago
*/

/**
* Tarjetas de Crédito o Débito
*/

/**
* 1. Tokeniza una tarjeta
*/
 $token = Wompi::tokenize_card(
        [
            "number" => "4242424242424242", // Número de la tarjeta
            "cvc" => "123", // Código de seguridad de la tarjeta (3 o 4 dígitos según corresponda)
            "exp_month" => "08", // Mes de expiración (string de 2 dígitos)
            "exp_year" => "28", // Año expresado en 2 dígitos
            "card_holder" => "José Pérez" // Nombre del tarjetahabiente
        ]
    );

/**
* 2. Realiza la transacción
*/
  $paymentCard = Wompi::card(
        $acceptance_token,
        $token->data->id,
        2, // Número de cuotas
         [
        "amount_in_cents" => 30300000,
        "currency" => "COP",
        "customer_email" => "user@test.com",
        "reference" => '0000000000',
         // Otros campos de la transacción a crear...
        ]
    );
    
 /**
* Botón de Transferencia Bancolombia
*/

    $bancolombia =  Wompi::bancolombia(
        $this->acceptance_token,
        $payment_description,
        [
        "amount_in_cents" => 30300000,
        "currency" => "COP",
        "customer_email" => "user@test.com",
        "reference" => '0000000000',
         // Otros campos de la transacción a crear...
        ]
    );
    
/**
* Nequi
*/
    $nequi =  Wompi::nequi(
        $this->acceptance_token,
        $phone,
         [
        "amount_in_cents" => 30300000,
        "currency" => "COP",
        "customer_email" => "user@test.com",
        "reference" => '0000000000',
         // Otros campos de la transacción a crear...
        ]
    );
 
/**
* PSE
*/
    $pse = Wompi::pse(
        $this->acceptance_token,
         0, // Tipo de persona, natural (0) o jurídica (1)
        'CC', // Tipo de documento, CC o NIT
        '985874589', // Número de documento
        $responses->data[0]->financial_institution_code, // Código (`code`) de la institución financiera
        $payment_description, // Nombre de lo que se está pagando. Máximo 30 caracteres
        [
        "amount_in_cents" => 30300000,
        "currency" => "COP",
        "customer_email" => "user@test.com",
        "reference" => '0000000000',
         // Otros campos de la transacción a crear...
        ]

    );

```
