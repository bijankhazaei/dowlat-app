# Payment System Documentation

## Overview

This payment system integrates with Zibal payment gateway using the Shetabit Multipay package. The system handles meal reservation payments with proper transaction tracking and validation.

## Architecture

### Key Components

1. **PaymentService** - Core payment logic
2. **PaymentController** - HTTP endpoints for payment operations
3. **Transaction Model** - Database representation of payment transactions
4. **Payment Model** - Payment records linked to meal reservations
5. **Zibal Driver** - Gateway integration via Shetabit Multipay

### Payment Flow

```
1. User initiates payment → Transaction created with 'init' status
2. PaymentService::request() → Calls Zibal API to get payment URL
3. User redirected to Zibal → Transaction status becomes 'pending'
4. User completes payment → Zibal redirects to callback URL
5. PaymentService::validate() → Verifies payment with Zibal
6. Transaction status updated → Payment and order status updated
```

## Configuration

### Zibal Settings (`config/payment.php`)

```php
'zibal' => [
    'apiPurchaseUrl' => 'https://gateway.zibal.ir/v1/request',
    'apiPaymentUrl' => 'https://gateway.zibal.ir/start/',
    'apiVerificationUrl' => 'https://gateway.zibal.ir/v1/verify',
    'merchantId' => env('APP_ENV') !== "production" ? "zibal" : "67b2ed626f380300090c2998",
    'callbackUrl' => env('ZIBAL_CALLBACK_URL', 'http://panel.dabestandowlat.com/api/payments/verify'),
    'description' => 'Zeenome Longevity',
    'currency' => 'T', // Toman
],
```

## API Endpoints

### Payment Request
- **URL**: `/pay/{transaction}`
- **Method**: GET
- **Middleware**: `auth`
- **Purpose**: Redirect user to payment gateway

### Payment Callback
- **URL**: `/payment/callback`
- **Method**: GET
- **Purpose**: Handle Zibal callback after payment

### Payment Verification
- **URL**: `/api/payments/verify`
- **Method**: ANY
- **Middleware**: `auth.zibal`
- **Purpose**: API endpoint for payment verification

## Transaction States

```php
enum ETransactionStates: string
{
    case Init = 'init';        // Transaction created, ready for payment
    case Pending = 'pending';  // Payment request sent to gateway
    case Success = 'success';  // Payment completed successfully
    case Error = 'error';      // Payment failed
    case Expired = 'expired';  // Payment expired
}
```

## Payment States

```php
enum EPaymentStates: string
{
    case Unpaid = 'unpaid';       // Payment not completed
    case Paid = 'paid';           // Payment completed
    case Error = 'error';         // Payment error
    case Cancelled = 'cancelled'; // Payment cancelled
}
```

## Key Methods

### PaymentService::request()

Initiates payment request with Zibal gateway.

```php
public static function request(Transaction &$transaction, $authCustomerId): string
```

**Parameters:**
- `$transaction`: Transaction object in 'init' status
- `$authCustomerId`: Customer mobile number

**Returns:** Gateway URL for payment

**Throws:** `RuntimeException` if transaction is not in 'init' status

### PaymentService::validate()

Validates payment with Zibal and updates transaction status.

```php
public static function validate(Transaction &$transaction): void
```

**Parameters:**
- `$transaction`: Transaction object to validate

**Side Effects:**
- Updates transaction status and metadata
- Updates payment status if successful
- Updates meal reservation status if needed

## Error Handling

### Zibal Status Codes

The system handles various Zibal response codes:

- `100`: Success
- `201`: Previously verified
- `202`: Payment not completed
- `203`: Invalid trackId
- And many more error codes with Persian translations

### Exception Types

- `PurchaseFailedException`: Payment request failed
- `InvalidPaymentException`: Invalid payment data
- `PreviouslyVerifiedException`: Payment already verified

## Security

### Callback Authentication

The `ZibalCallbackAuthentication` middleware validates:
- Signed URLs to prevent tampering
- User ID parameter validation
- Transaction existence verification
- Status code validation

### Data Validation

- trackId parameter validation
- Transaction status checks
- User authentication for callbacks

## Testing

### Unit Tests
- `PaymentServiceTest`: Tests core payment logic
- Configuration validation
- Exception handling

### Feature Tests
- `PaymentTest`: End-to-end payment flow testing
- Callback endpoint testing
- Database integration testing

## Troubleshooting

### Common Issues

1. **"Transaction is not open to pay"**
   - Check transaction status is 'init'
   - Ensure transaction hasn't been processed already

2. **Callback not working**
   - Verify callback URL configuration
   - Check middleware authentication
   - Validate trackId parameter

3. **Payment verification fails**
   - Check Zibal merchant ID
   - Verify API endpoints are correct
   - Check network connectivity

### Debugging

Enable logging in `PaymentService` methods to track:
- API requests/responses
- Transaction state changes
- Error conditions

## Environment Variables

```env
ZIBAL_CALLBACK_URL=http://panel.dabestandowlat.com/api/payments/verify
APP_ENV=production  # Use "zibal" merchant ID for testing
```

## Dependencies

- `shetabit/multipay`: Payment gateway abstraction
- `guzzlehttp/guzzle`: HTTP client for API calls
- Laravel framework components

## Future Improvements

1. Add payment retry mechanism
2. Implement webhook handling
3. Add payment analytics
4. Support multiple payment providers
5. Add payment scheduling
6. Implement refund functionality