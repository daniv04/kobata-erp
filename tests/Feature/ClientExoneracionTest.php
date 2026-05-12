<?php

namespace Tests\Feature;

use App\Enums\NombreInstitucionExoneracion;
use App\Enums\TipoDocumentoExoneracion;
use App\Models\Client;
use App\Models\ClientExoneracion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ClientExoneracionTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'hacienda_name' => 'Zona Franca Test S.A.',
            'id_number_type' => 'juridica',
            'id_number' => '3101000099',
        ]);
    }

    public function test_client_can_have_exoneracion(): void
    {
        $exoneracion = ClientExoneracion::create([
            'client_id' => $this->client->id,
            'tipo_documento' => TipoDocumentoExoneracion::ZonaFranca,
            'numero_documento' => 'ZF-2024-001',
            'nombre_institucion' => NombreInstitucionExoneracion::Hacienda,
            'fecha_emision' => now(),
            'tarifa_exonerada' => 13.00,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('client_exoneraciones', [
            'client_id' => $this->client->id,
            'tipo_documento' => '08',
            'numero_documento' => 'ZF-2024-001',
            'is_active' => true,
        ]);

        $this->assertTrue($this->client->activeExoneracion->is($exoneracion));
    }

    public function test_only_one_exoneracion_can_be_active_at_a_time(): void
    {
        $first = ClientExoneracion::create([
            'client_id' => $this->client->id,
            'tipo_documento' => TipoDocumentoExoneracion::ZonaFranca,
            'numero_documento' => 'ZF-2023-001',
            'nombre_institucion' => NombreInstitucionExoneracion::Hacienda,
            'fecha_emision' => now()->subYear(),
            'tarifa_exonerada' => 13.00,
            'is_active' => true,
        ]);

        // Deactivate first manually (as the RelationManager does via ->after())
        $this->client->exoneraciones()->where('id', '!=', $first->id)->update(['is_active' => false]);

        $second = ClientExoneracion::create([
            'client_id' => $this->client->id,
            'tipo_documento' => TipoDocumentoExoneracion::ZonaFranca,
            'numero_documento' => 'ZF-2024-001',
            'nombre_institucion' => NombreInstitucionExoneracion::Hacienda,
            'fecha_emision' => now(),
            'tarifa_exonerada' => 13.00,
            'is_active' => true,
        ]);

        $this->client->exoneraciones()->where('id', '!=', $second->id)->update(['is_active' => false]);

        $this->assertDatabaseHas('client_exoneraciones', ['id' => $first->id, 'is_active' => false]);
        $this->assertDatabaseHas('client_exoneraciones', ['id' => $second->id, 'is_active' => true]);
        $this->assertTrue($this->client->fresh()->activeExoneracion->is($second));
    }

    public function test_active_exoneracion_endpoint_returns_null_when_none(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('clientes.exoneracion-activa', $this->client));

        $response->assertNoContent();
    }

    public function test_active_exoneracion_endpoint_returns_exoneracion_data(): void
    {
        $user = User::factory()->create();

        ClientExoneracion::create([
            'client_id' => $this->client->id,
            'tipo_documento' => TipoDocumentoExoneracion::ZonaFranca,
            'numero_documento' => 'ZF-2024-001',
            'nombre_institucion' => NombreInstitucionExoneracion::Hacienda,
            'fecha_emision' => '2024-01-15 00:00:00',
            'tarifa_exonerada' => 13.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('clientes.exoneracion-activa', $this->client));

        $response->assertOk()
            ->assertJsonFragment([
                'tipo_documento' => '08',
                'numero_documento' => 'ZF-2024-001',
                'nombre_institucion' => '01',
                'tarifa_exonerada' => 13.0,
            ]);
    }

    public function test_client_without_active_exoneracion_returns_null_from_relationship(): void
    {
        ClientExoneracion::create([
            'client_id' => $this->client->id,
            'tipo_documento' => TipoDocumentoExoneracion::ZonaFranca,
            'numero_documento' => 'ZF-2023-001',
            'nombre_institucion' => NombreInstitucionExoneracion::Hacienda,
            'fecha_emision' => now(),
            'tarifa_exonerada' => 13.00,
            'is_active' => false,
        ]);

        $this->assertNull($this->client->fresh()->activeExoneracion);
    }
}
