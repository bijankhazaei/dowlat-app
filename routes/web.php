<?php

use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('admin');
});

// make payment callback route
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

Route::get('/pay/{transaction}', [PaymentController::class, 'redirectToGateway'])
    ->middleware(['auth'])
    ->name('filament.pay');
