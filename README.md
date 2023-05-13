# Multi Payment Gateway

Multi Payment Gateway refers to the ability to accept payments from customers through multiple payment processors or gateways. It means that a business can provide its customers with various options to pay for goods and services, such as credit card, debit card, bank transfers, PayPal, and more. By offering multiple payment gateways, businesses can increase their sales by providing more payment options and reduce the risk of lost sales due to payment issues. This can also help to improve customer experience by making the payment process smoother and more convenient. Integration of multiple payment gateways can be done through the use of payment gateway APIs or payment gateway integrations provided by e-commerce platforms.

## Installation
1. Clone the repository to your local machine.
2. Run composer install to install dependencies.
3. Configure your .env file with your credentials.
4. Start the development server with php artisan serve.

## Project Structure

```php
app/
├── Http/
│   ├── Controllers/
│   │   └── PaymentController.php
├── Providers/
│   └── AppServiceProvider.php
├── Services/
│   ├── PaymentService.php
│   └── TheTellerPaymentService.php
├── routes/
│   └── api.php
├── .env
├── .env.example
├── composer.json
└── README.md
```
## API Endpoints

The following endpoints are available in this API:

- `/pay`: This endpoint handles requests related to riders.
- `/paystack-webhook`: This endpoint handles callback requests related to the paystack payment gateway.

## Controller Details

The following is a list of controllers and their corresponding files:

- `PaymentController`: `app/Controllers/api/PaymentController.php`

## Trait Methods

The following is a list of Traits methods and their corresponding files:

- `PaymentService`: `app/Services/PaymentService.php`
- `TheTellerPaymentService`: `app/Services/TheTellerPaymentService.php`

