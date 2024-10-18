# PhonePe Payment Gateway for Laravel

This package provides an easy way to integrate the PhonePe payment gateway into your Laravel application.

## Features

- Initiate payments via PhonePe.
- Check transaction statuses.
- Secure callback handling for payment notifications.

## Installation

### Step 1: Install the Package
Run the following command to install the package via Composer:

```bash
composer require zarenta/phonepe
```



### Step 2: Publish Configuration


Add Service provider to `bootstrap/provider.php` file

```bash

PhonePe\LaravelPhonePeServiceProvider::class,

```

Publish the configuration file using the artisan command:

```bash

php artisan vendor:publish --tag=phonepe-config

```

This will publish a `config/phonepe.php` file where you can set your PhonePe credentials.

### Step 3: Add CSRF Exception for Callback URL

Update `bootstrap/app.php` to exclude the callback URL from CSRF verification. Add the following middleware configuration:

```php

->withMiddleware(function (Middleware $middleware) {

    $middleware->web(append: [

        \App\Http\Middleware\HandleInertiaRequests::class,

        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,

    ]);

    $middleware->validateCsrfTokens(except: [

        'phonepe/callback',  // Exclude PhonePe callback route from CSRF

    ]);

})

```

### Step 4: Set Up Environment Variables

In your `.env` file, add the following variables with your PhonePe account details:

```env

PHONEPE_MERCHANT_ID=your_merchant_id

PHONEPE_MERCHANT_USER_ID=your_merchant_user_id

PHONEPE_SALT_KEY=your_salt_key

PHONEPE_SALT_INDEX=your_salt_index

PHONEPE_CALLBACK_URL=https://yourdomain.com/phonepe/callback

PHONEPE_ENV=production # or sandbox for testing

```

## Configuration

The published configuration file `config/phonepe.php` will have the following structure:

```php

return [

    'merchantId' => env('PHONEPE_MERCHANT_ID'),

    'merchantUserId' => env('PHONEPE_MERCHANT_USER_ID'),

    'saltKey' => env('PHONEPE_SALT_KEY'),

    'saltIndex' => env('PHONEPE_SALT_INDEX'),

    'callBackUrl' => env('PHONEPE_CALLBACK_URL'),

    'env' => env('PHONEPE_ENV', 'sandbox'),

];

```

## Usage

### Initiating Payment

Here's an example of how to initiate a payment using the `PhonePeGateway`:

```php

use PhonePe\PhonePeGateway;

public function initiatePayment(Request $request)

{

    $phonePe = new PhonePeGateway();

    try {

        $paymentUrl = $phonePe->makePayment(

            $amount = 1000, // Amount in rupees

            $redirectUrl = 'https://yourdomain.com/payment/success',

            $merchantTransactionId = 'your_unique_transaction_id',

            $phone = '9999999999',

            $email = 'user@example.com',

            $shortName = 'Your Company',

            $message = 'Payment for Order #1234'

        );

        return redirect($paymentUrl);

    } catch (PhonePe\Exception\PhonePeException $e) {

        return response()->json(['error' => $e->getMessage()], 400);

    }

}

```

### Checking Transaction Status

To check the transaction status, use the `getTransactionStatus` method:

```php

use PhonePe\PhonePeGateway;

public function checkTransactionStatus($transactionId)

{

    $phonePe = new PhonePeGateway();

    $transactionData = [

        'merchantId' => config('phonepe.merchantId'),

        'transactionId' => $transactionId,

    ];

    $status = $phonePe->getTransactionStatus($transactionData);

    if ($status) {

        return response()->json(['status' => 'Transaction successful']);

    } else {

        return response()->json(['status' => 'Transaction failed or pending']);

    }

}

```

### Handling PhonePe Callback

Create a route to handle the PhonePe callback in your `routes/web.php`:

```php

Route::post('/phonepe/callback', [YourPaymentController::class, 'handlePhonePeCallback']);

```

In your controller, you can write logic to handle the callback and update the transaction status accordingly.

## License

This package is open-source and licensed under the [MIT License](LICENSE).



