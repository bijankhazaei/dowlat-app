<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\DevController;
use App\Http\Controllers\Api\ExternalServicesController;
use App\Http\Controllers\Api\JourneyController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

// Check if Api is Working
Route::get('/ping', function () {
    return response()->apiResult("pong");
});
Route::any('/dev/test', [DevController::class, 'test']);

#region: Auth

Route::group(['prefix' => 'auth'], function () {
    // Routes for login or register with mobile by OTP
    Route::post('/sign-in', [AuthController::class, 'signIn']);
    Route::post('/sign-validate', [AuthController::class, 'signValidate']);
    Route::middleware(['auth:api'])->post('/sign-out', [AuthController::class, 'signOut']);
});

#endregion: Auth

// Public Routes
Route::prefix('journeys')->group(function () {
    Route::get('/', [JourneyController::class, 'list']);
    Route::get('/{journey}', [JourneyController::class, 'show']);
});

// Authenticated Routes
Route::middleware(['auth:api'])->group(function () {

    Route::get('/my-journeys', [JourneyController::class, 'myJourneys']);

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getProfile']);
        Route::post('/', [ProfileController::class, 'setProfile']);
    });

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'details']);
        Route::post('/add', [CartController::class, 'addItem']);
        Route::post('/set-extras', [CartController::class, 'setExtras']);
        Route::post('/reduce', [CartController::class, 'reduceCount']);
        Route::post('/remove', [CartController::class, 'removeItem']);
        Route::post('/clear', [CartController::class, 'clear']);
    });

    Route::middleware(['profile.completed'])->group(function () {
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'list']);
            Route::post('/', [OrderController::class, 'submitOrder']);
            Route::get('/{order}', [OrderController::class, 'show']);
            Route::post('/{order}/retry-payment', [OrderController::class, 'retryPayment']);
            Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
            Route::post('/{order}/longevity-score/{longevity_score_oj}/submit-lab-test', [OrderController::class, 'submitLabTest']);
        });

        Route::prefix('payments')->group(function () {
            Route::post('/submit', [PaymentController::class, 'submitPayment']);
            Route::withoutMiddleware(['auth:api', 'profile.completed'])
                ->middleware(['auth.zibal'])
                ->any('/verify', [PaymentController::class, 'verifyPayment'])
                ->name('payment-verify');
            Route::get('status/{payment_id}', [PaymentController::class, 'paymentStatus']);
        });
    });
});


Route::prefix('ws')->group(function () {
    Route::any('/porsline', [ExternalServicesController::class, 'porsline'])->name('ws-porsline');
});
