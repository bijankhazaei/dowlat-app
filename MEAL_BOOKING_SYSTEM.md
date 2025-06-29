# Meal Booking System with Zibal Payment

## Overview
Complete meal booking system where users can reserve meals, pay with Zibal gateway, and admins can manage reservations through Filament.

## System Flow

### 1. User Books Meals
- User selects meals from `MealBooking` page
- System creates `MealReservation` with status `pending`
- Creates `MealReservationItem` for each selected meal
- Creates `Payment` record with status `unpaid`
- Creates `Transaction` with status `init`
- Redirects user to Zibal payment gateway

### 2. Payment Processing
- User completes payment on Zibal
- Zibal redirects to callback URL
- System validates payment with Zibal API
- Updates transaction status based on payment result

### 3. Success Flow
- Transaction status → `success`
- Payment status → `paid`
- MealReservation status → `completed`
- User sees success message

### 4. Failure Flow
- Transaction status → `error`
- Payment status remains `unpaid`
- MealReservation status → `cancelled`
- User sees failure message

## Database Structure

### MealReservation
- `id` - Primary key
- `user_id` - Foreign key to users
- `status` - pending/completed/cancelled
- `price` - Total reservation price
- `created_at`, `updated_at`

### MealReservationItem
- `id` - Primary key
- `meal_reservation_id` - Foreign key
- `meal_id` - Foreign key to meals
- `reservation_date` - Date for the meal
- `price` - Individual meal price

### Payment
- `id` - Primary key
- `meal_reservation_id` - Foreign key
- `amount` - Payment amount
- `summary` - Payment description
- `status` - unpaid/paid/error/cancelled

### Transaction
- `id` - Primary key
- `payment_id` - Foreign key
- `status` - init/pending/success/error/expired
- `amount` - Transaction amount
- `provider` - Payment provider (zibal)
- `authority` - Zibal transaction ID
- `reference` - Payment reference number
- `metadata` - Additional data (JSON)

## Key Components

### MealBooking Page (`app/Filament/Pages/MealBooking.php`)
- Displays available meals by day/week
- Handles meal selection
- Creates reservation and payment
- Redirects to payment gateway

### PaymentService (`app/Services/Payment/PaymentService.php`)
- `request()` - Initiates payment with Zibal
- `validate()` - Verifies payment and updates status

### PaymentController (`app/Http/Controllers/Api/PaymentController.php`)
- `callback()` - Handles Zibal callback
- `verifyPayment()` - API endpoint for verification

### MealReservationResource (`app/Filament/Resources/MealReservationResource.php`)
- Admin interface for managing reservations
- Shows reservation details, payment status
- Filterable by status
- View individual reservation with items

## Filament Admin Features

### Reservation Management
- List all meal reservations
- Filter by status (pending/completed/cancelled)
- View reservation details including:
  - User information
  - Payment status
  - Individual meal items
  - Dates and prices

### Status Indicators
- Reservation status badges
- Payment status badges
- Color-coded for easy identification

## Payment Integration

### Zibal Configuration
```php
'zibal' => [
    'merchantId' => env('APP_ENV') !== "production" ? "zibal" : "67b2ed626f380300090c2998",
    'apiPurchaseUrl' => 'https://gateway.zibal.ir/v1/request',
    'apiPaymentUrl' => 'https://gateway.zibal.ir/start/',
    'apiVerificationUrl' => 'https://gateway.zibal.ir/v1/verify',
    'callbackUrl' => 'http://panel.dabestandowlat.com/api/payments/verify',
    'currency' => 'T', // Toman
]
```

### Security Features
- Signed callback URLs
- Transaction validation
- User authentication for callbacks
- Status verification

## Testing

### Unit Tests
- Payment service logic
- Transaction state management
- Configuration validation

### Feature Tests
- Complete booking flow
- Success/failure scenarios
- Database state verification

## Usage

### For Users
1. Navigate to "رزرو غذا" in Filament
2. Select desired meals for different days
3. Click submit to create reservation
4. Complete payment on Zibal gateway
5. Return to see confirmation

### For Admins
1. Navigate to "رزرو غذاها" in Filament admin
2. View all reservations with status
3. Filter by status or search by user
4. Click on reservation to see details
5. Edit reservation if needed

## Status Meanings

### Reservation Status
- `pending` - Waiting for payment
- `completed` - Payment successful, reservation confirmed
- `cancelled` - Payment failed or cancelled

### Payment Status
- `unpaid` - Payment not completed
- `paid` - Payment successful
- `error` - Payment failed

### Transaction Status
- `init` - Transaction created, ready for payment
- `pending` - Payment request sent to gateway
- `success` - Payment completed successfully
- `error` - Payment failed
- `expired` - Payment expired

## Error Handling
- Database transactions for data consistency
- Proper exception handling
- User-friendly error messages
- Automatic status updates on failure