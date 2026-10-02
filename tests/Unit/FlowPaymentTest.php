<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\FlowService;
use PHPUnit\Framework\TestCase;

class FlowPaymentTest extends TestCase
{
    public function test_signature_generation_matches_flow_specification(): void
    {
        $flow = new FlowService();

        // Reflection to set secret key for testing
        $reflector = new \ReflectionClass($flow);
        $secretProp = $reflector->getProperty('secretKey');
        $secretProp->setAccessible(true);
        $secretProp->setValue($flow, 'secret123');

        $params = [
            'apiKey' => 'api_key_test',
            'commerceOrder' => '1001',
            'amount' => 15000,
            'currency' => 'CLP',
            'email' => 'juan@ejemplo.cl',
        ];

        // Flow specification:
        // 1. Sort alphabetically by key: amount, apiKey, commerceOrder, currency, email
        // 2. Concatenate key + value without separator:
        //    amount15000apiKeyapi_key_testcommerceOrder1001currencyCLPemailjuan@ejemplo.cl
        // 3. HMAC-SHA256 with secret key
        $expectedString = 'amount15000apiKeyapi_key_testcommerceOrder1001currencyCLPemailjuan@ejemplo.cl';
        $expectedSignature = hash_hmac('sha256', $expectedString, 'secret123');

        $actualSignature = $flow->sign($params);

        $this->assertEquals($expectedSignature, $actualSignature);
    }

    public function test_simulation_fallback_when_credentials_unconfigured(): void
    {
        $flow = new FlowService();

        // Reflection to empty credentials
        $reflector = new \ReflectionClass($flow);
        $apiProp = $reflector->getProperty('apiKey');
        $apiProp->setAccessible(true);
        $apiProp->setValue($flow, '');

        $this->assertFalse($flow->isConfigured());
    }
}
