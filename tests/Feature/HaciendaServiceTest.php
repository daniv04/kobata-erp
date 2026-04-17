<?php

namespace Tests\Feature;

use App\Services\HaciendaService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class HaciendaServiceTest extends TestCase
{
    public function test_retorna_datos_del_contribuyente_cuando_la_api_responde_ok(): void
    {
        Http::fake([
            'api.hacienda.go.cr/*' => Http::response([
                'nombre' => 'JOSE DANIEL VILLALOBOS OROZCO',
                'tipoIdentificacion' => '01',
                'regimen' => ['codigo' => 1, 'descripcion' => 'Régimen general'],
                'situacion' => [
                    'moroso' => 'NO',
                    'omiso' => 'NO',
                    'estado' => 'Inscrito',
                    'administracionTributaria' => 'Alajuela',
                ],
                'actividades' => [
                    ['estado' => 'A', 'tipo' => 'P', 'codigo' => '6201.0', 'descripcion' => 'Actividades de programación informática'],
                ],
            ], 200),
        ]);

        $datos = app(HaciendaService::class)->consultarContribuyente('2100042005');

        $this->assertSame('JOSE DANIEL VILLALOBOS OROZCO', $datos['nombre']);
        $this->assertSame('Inscrito', $datos['situacion']['estado']);
        $this->assertCount(1, $datos['actividades']);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'identificacion=2100042005'));
    }

    public function test_lanza_excepcion_cuando_la_cedula_no_existe(): void
    {
        Http::fake([
            'api.hacienda.go.cr/*' => Http::response(null, 404),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No se encontró información');

        app(HaciendaService::class)->consultarContribuyente('0000000000');
    }

    public function test_lanza_excepcion_cuando_la_api_falla(): void
    {
        Http::fake([
            'api.hacienda.go.cr/*' => Http::response(null, 500),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTP 500');

        app(HaciendaService::class)->consultarContribuyente('2100042005');
    }
}
