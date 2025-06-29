<?php

namespace Tests\Unit;

use App\Contracts\Enums\ETransactionStates;
use App\Models\Transaction;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use PHPUnit\Framework\TestCase;
use Mockery;

class PaymentServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_payment_request_throws_exception_for_non_init_transaction()
    {
        // Create a mock transaction with non-init status
        $transaction = Mockery::mock(Transaction::class);
        $transaction->status = ETransactionStates::Pending;
        
        // Expect exception to be thrown
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transaction is not open to pay.');
        
        PaymentService::request($transaction, '09123456789');
    }

    public function test_validate_returns_early_for_non_pending_transaction()
    {
        // Create a mock transaction with non-pending status
        $transaction = Mockery::mock(Transaction::class);
        $transaction->status = ETransactionStates::Success;
        
        // This should return early without doing anything
        PaymentService::validate($transaction);
        
        // If we get here without exception, the test passes
        $this->assertTrue(true);
    }

    public function test_zibal_configuration_is_correct()
    {
        // Test that Zibal is configured as default provider
        $this->assertEquals('zibal', config('payment.default'));
        
        // Test Zibal configuration exists
        $zibalConfig = config('payment.drivers.zibal');
        $this->assertNotNull($zibalConfig);
        $this->assertArrayHasKey('merchantId', $zibalConfig);
        $this->assertArrayHasKey('apiPurchaseUrl', $zibalConfig);
        $this->assertArrayHasKey('apiPaymentUrl', $zibalConfig);
        $this->assertArrayHasKey('apiVerificationUrl', $zibalConfig);
    }
}