<p align="center">
  <img src="https://comunidad.apphive.io/uploads/default/original/2X/4/4e6b2a93f2962dcf06f8683c105cfdd64d3d18b3.png" alt="Logo Laravel Cashier Stripe" width="200px">
</p>

<p align="center">
<a href="https://packagist.org/packages/bancolombia-dev/wompi-php"><img src="https://img.shields.io/packagist/dt/bancolombia-dev/wompi-php" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/bancolombia-dev/wompi-php"><img src="https://img.shields.io/packagist/v/bancolombia-dev/wompi-php" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/bancolombia-dev/wompi-php"><img src="https://img.shields.io/packagist/l/bancolombia-dev/wompi-php" alt="License"></a>
 <a href="https://github.com/oscar-rey-mosquera/wompi-php/actions/workflows/test.yml"><img src="https://github.com/oscar-rey-mosquera/wompi-php/actions/workflows/test.yml/badge.svg" alt="Test"></a>
</p>

## Introducción

Este es un paquete de la api de [wompi](https://wompi.co/) que pertenece al grupo Bancolombia para php.

## 💻 Instalación

Para instalar utiliza [composer](https://getcomposer.org/).

```.bash  
composer require bancolombia-dev/wompi-php
```

## Test

Dependiendo de la llave pública y llave privada es el entorno de trabajo.

## 🔧 Uso del paquete

Para iniciar a utilizar el paquete necesitas tu llave pública y llave privada de tu [cuenta wompi](https://comercios.wompi.co/developers).

```php
  /**
   * Autocarga de clases  
   */
require_once 'vendor/autoload.php';

use Bancolombia\Wompi;

Wompi::initialize([
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
        $acceptance_token,
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
        $acceptance_token,
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
        $acceptance_token,
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
    
/**
* Pago en efectivo en Corresponsales Bancarios Bancolombia
*/
  //1 Crea la transacción  

    $bancolombia_collect =  Wompi::bancolombia_collect(
        $acceptance_token,
        [
        "amount_in_cents" => 30300000,
        "currency" => "COP",
        "customer_email" => "user@test.com",
        "reference" => '0000000000',
         // Otros campos de la transacción a crear...
        ]

    );
    
   //2 Consulta la transacción creada
     $transaction = Wompi::transaction_find_by_id($bancolombia_collect->data->id);

```

## Fuentes de pago & Tokenización

```php
  /**
   * Autocarga de clases  
   */
require_once 'vendor/autoload.php';

use Bancolombia\Wompi;

Wompi::initialize([
    'public_key' => '',
    'private_key' => ''
  ]);
  
  // Tokenización cuentas Nequi
   $tokenNequi =  Wompi::tokenize_nequi($phone);
   
 // para chequear el estado de la suscripción
   $subscription = Wompi::subscription_nequi($tokenNequi->data->id);
   
 //** Nota: Para tarjetas de crédito se tokeniza de la misma forma cuando se va a realizar un pago
 
 // Crea una fuente de pago 
  Wompi::payment_sources(
        $tokenizeId,
        $customer_email,
        $acceptance_token
    );
    
 //  Crea una transacción con fuente de pago
   Wompi::transaction(
      [
         "payment_source_id" => 3891 // ID de la fuente de pago
         "amount_in_cents" => 30300000,
        "currency" => "COP",
        "customer_email" => "user@test.com",
        "reference" => '0000000000',
         // Otros campos de la transacción a crear...
      ]
    );
   
```

## Links de pago

```php
  /**
   * Autocarga de clases  
   */
require_once 'vendor/autoload.php';

use Bancolombia\Wompi;

Wompi::initialize([
    'public_key' => '',
    'private_key' => ''
  ]);
  
 /**
* Crear link de pago
* @link https://docs.wompi.co/docs/en/links-de-pago
*/

$link = Wompi::link([
    "name" => "Pago de arriendo edificio Lombardía - AP 505", // Nombre del link de pago
    "description" => "Arriendo mensual", // Descripción del pago
    "single_use" => false, // `false` en caso de que el link de pago pueda recibir múltiples transacciones APROBADAS o `true` si debe dejar de aceptar        transacciones después del primer pago APROBADO
    "collect_shipping" => false // Si deseas que el cliente inserte su información de envío en el checkout, o no
    // Otros campos de la transacción a crear...
]);

$link['response']; // respuesta
$link['link']; // link de pago

```

## Anula una transacción

Anula una transacción APROBADA. Aplica únicamente para transacciones con Tarjeta (tipo CARD).

```php
require_once 'vendor/autoload.php';

use Bancolombia\Wompi;

Wompi::initialize([
    'public_key' => '',
    'private_key' => ''
  ]);
  
Wompi::cancell_transaction($transaction_id);
```

## Verifica la autenticidad de un evento (webhook)

Por seguridad necesitas  checkear eventos de webhook y para eso necesitas tu [secret event](https://comercios.wompi.co/developers).

```php
require_once 'vendor/autoload.php';

use Bancolombia\Wompi;

Wompi::initialize([
    'public_key' => '',
    'private_key' => '',
    //Agregar event key
    'private_event_key' => '' 
  ]);

//@link https://www.geeksforgeeks.org/how-to-receive-json-post-with-php
$request = file_get_contents('php://input');

//@return bool
Wompi::check_webhook(json_decode($request, true)));
```

## Contribución

Puedes contribuir agregando nuevas funcionalidades, actualizaciones,  refactorización de código y notificando errores, con antelación se agradece.

## License

[MIT license](LICENSE).
