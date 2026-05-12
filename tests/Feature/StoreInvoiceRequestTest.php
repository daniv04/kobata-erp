<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Products;
use App\Models\User;
use App\Services\Facturacion\FacturacionService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StoreInvoiceRequestTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Client $client;

    private Products $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'hacienda_name' => 'Cliente Test S.A.',
            'id_number_type' => 'juridica',
            'id_number' => '3101000001',
        ]);

        $this->product = Products::create([
            'sku' => 'TEST-001',
            'name' => 'Producto Test',
            'cabys_code' => '4111000000000',
            'sale_price' => 1000,
            'distributor_price' => 900,
            'tax_percentage' => 13,
        ]);
    }

    private function validPayload(): array
    {
        return [
            'client_id' => $this->client->id,
            'currency' => 'CRC',
            'payment_methods' => [
                ['type' => '01', 'amount' => 1130, 'others_description' => null],
            ],
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'name' => 'Producto Test',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'tax_percentage' => 13,
                    'discount_enabled' => false,
                    'discount_type' => null,
                    'discount_percentage' => null,
                ],
            ],
        ];
    }

    private function actingAsAdmin(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_payload_valido_pasa_validacion(): void
    {
        $this->mock(FacturacionService::class)
            ->shouldReceive('enviar')
            ->once()
            ->andReturn(['success' => true]);

        $response = $this->actingAsAdmin()->postJson('/panel/facturacion', $this->validPayload());

        $response->assertOk();
    }

    public function test_currency_es_requerido(): void
    {
        $payload = $this->validPayload();
        unset($payload['currency']);

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency');
    }

    public function test_currency_invalido_falla(): void
    {
        $payload = $this->validPayload();
        $payload['currency'] = 'JPY';

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency');
    }

    public function test_payment_methods_es_requerido(): void
    {
        $payload = $this->validPayload();
        unset($payload['payment_methods']);

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_methods');
    }

    public function test_mas_de_cuatro_metodos_de_pago_falla(): void
    {
        $payload = $this->validPayload();
        $payload['payment_methods'] = array_fill(0, 5, ['type' => '01', 'amount' => 200]);

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_methods');
    }

    public function test_tipo_metodo_invalido_falla(): void
    {
        $payload = $this->validPayload();
        $payload['payment_methods'] = [['type' => '08', 'amount' => 1130]];

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_methods.0.type');
    }

    public function test_metodo_otros_sin_descripcion_falla(): void
    {
        $payload = $this->validPayload();
        $payload['payment_methods'] = [['type' => '99', 'amount' => 1130, 'others_description' => null]];

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_methods.0.others_description');
    }

    public function test_monto_negativo_falla(): void
    {
        $payload = $this->validPayload();
        $payload['payment_methods'] = [['type' => '01', 'amount' => -100]];

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_methods.0.amount');
    }

    public function test_items_vacio_falla(): void
    {
        $payload = $this->validPayload();
        $payload['items'] = [];

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');
    }

    public function test_client_id_inexistente_falla(): void
    {
        $payload = $this->validPayload();
        $payload['client_id'] = 99999;

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('client_id');
    }

    public function test_multiples_metodos_de_pago_validos_pasan(): void
    {
        $this->mock(FacturacionService::class)
            ->shouldReceive('enviar')
            ->once()
            ->andReturn(['success' => true]);

        $payload = $this->validPayload();
        $payload['payment_methods'] = [
            ['type' => '01', 'amount' => 500],
            ['type' => '02', 'amount' => 630],
        ];

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertOk();
    }

    public function test_metodo_otros_con_descripcion_valida_pasa(): void
    {
        $this->mock(FacturacionService::class)
            ->shouldReceive('enviar')
            ->once()
            ->andReturn(['success' => true]);

        $payload = $this->validPayload();
        $payload['payment_methods'] = [
            ['type' => '99', 'amount' => 1130, 'others_description' => 'Criptomoneda Bitcoin'],
        ];

        $this->actingAsAdmin()->postJson('/panel/facturacion', $payload)
            ->assertOk();
    }
}
