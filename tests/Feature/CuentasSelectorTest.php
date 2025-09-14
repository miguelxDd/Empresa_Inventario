<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Cuenta;
use Illuminate\Support\Facades\Cache;

class CuentasSelectorTest extends TestCase
{
    // Usar base de datos existente en lugar de RefreshDatabase

    protected function setUp(): void
    {
        parent::setUp();
        
        // Los datos ya existen en la base de datos
    }

    /**
     * Test del endpoint cuentasSelect para contexto inventario
     */
    public function test_cuentas_select_contexto_inventario()
    {
        $response = $this->getJson('/contabilidad/cuentas/select?contexto=inventario');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'text',
                        'disabled',
                        'es_hoja',
                        'nivel',
                        'codigo',
                        'tipo'
                    ]
                ]);

        $cuentas = $response->json();
        
        // Verificar que todas las cuentas devueltas son de tipo activo
        foreach ($cuentas as $cuenta) {
            $this->assertEquals('activo', $cuenta['tipo']);
            $this->assertStringStartsWith('11', $cuenta['codigo'], 
                'Las cuentas de inventario deben empezar con 11');
        }
    }

    /**
     * Test del endpoint cuentasSelect para contexto costo
     */
    public function test_cuentas_select_contexto_costo()
    {
        $response = $this->getJson('/contabilidad/cuentas/select?contexto=costo');

        $response->assertStatus(200);

        $cuentas = $response->json();
        
        // Verificar que todas las cuentas devueltas son de tipo gasto
        foreach ($cuentas as $cuenta) {
            $this->assertEquals('gasto', $cuenta['tipo']);
            $this->assertStringStartsWith('5', $cuenta['codigo'], 
                'Las cuentas de costo deben empezar con 5');
        }
    }

    /**
     * Test del endpoint cuentasSelect para contexto contraparte
     */
    public function test_cuentas_select_contexto_contraparte()
    {
        $response = $this->getJson('/contabilidad/cuentas/select?contexto=contraparte');

        $response->assertStatus(200);

        $cuentas = $response->json();
        
        // Verificar que se devuelven cuentas de diferentes tipos
        $tipos = array_unique(array_column($cuentas, 'tipo'));
        $this->assertGreaterThan(1, count($tipos), 
            'El contexto contraparte debe incluir múltiples tipos de cuenta');
    }

    /**
     * Test de estructura jerárquica
     */
    public function test_estructura_jerarquica()
    {
        $response = $this->getJson('/contabilidad/cuentas/select?contexto=inventario');
        $cuentas = $response->json();

        // Verificar que hay cuentas de diferentes niveles
        $niveles = array_unique(array_column($cuentas, 'nivel'));
        $this->assertContains(1, $niveles, 'Debe haber cuentas de nivel 1');
        $this->assertContains(2, $niveles, 'Debe haber cuentas de nivel 2');

        // Verificar que las cuentas padre están marcadas como disabled
        foreach ($cuentas as $cuenta) {
            if (!$cuenta['es_hoja']) {
                $this->assertTrue($cuenta['disabled'], 
                    'Las cuentas padre deben estar deshabilitadas');
            }
        }

        // Verificar que las cuentas hoja no están disabled
        foreach ($cuentas as $cuenta) {
            if ($cuenta['es_hoja']) {
                $this->assertFalse($cuenta['disabled'], 
                    'Las cuentas hoja deben estar habilitadas');
            }
        }
    }

    /**
     * Test de búsqueda por término
     */
    public function test_busqueda_por_termino()
    {
        $response = $this->getJson('/contabilidad/cuentas/select?contexto=inventario&q=productos');
        $cuentas = $response->json();

        // Verificar que los resultados contienen el término buscado
        foreach ($cuentas as $cuenta) {
            $this->assertTrue(
                stripos($cuenta['text'], 'productos') !== false || 
                stripos($cuenta['codigo'], 'productos') !== false,
                'Los resultados deben contener el término buscado'
            );
        }
    }

    /**
     * Test de caché
     */
    public function test_cache_funcionamiento()
    {
        // Limpiar caché antes del test
        Cache::flush();

        // Primera llamada - debería cachear
        $response1 = $this->getJson('/contabilidad/cuentas/select?contexto=inventario');
        $this->assertFalse(Cache::missing('cuentas_select_inventario_' . md5('')));

        // Segunda llamada - debería usar caché
        $response2 = $this->getJson('/contabilidad/cuentas/select?contexto=inventario');
        
        $this->assertEquals($response1->json(), $response2->json(), 
            'Las respuestas deben ser idénticas cuando se usa caché');
    }

    /**
     * Test de contexto inválido
     */
    public function test_contexto_invalido()
    {
        $response = $this->getJson('/contabilidad/cuentas/select?contexto=invalido');

        $response->assertStatus(400)
                ->assertJson(['error' => 'Contexto inválido']);
    }

    /**
     * Test de validación de ProductoRequest
     */
    public function test_producto_request_validacion_cuentas()
    {
        // Obtener una cuenta de inventario (activo)
        $cuentaInventario = Cuenta::where('tipo', 'activo')
                                 ->where('codigo', 'LIKE', '11%')
                                 ->whereNull('padre_id', false) // Cuenta hoja
                                 ->first();

        // Obtener una cuenta de costo (gasto)
        $cuentaCosto = Cuenta::where('tipo', 'gasto')
                            ->where('codigo', 'LIKE', '5%')
                            ->whereNull('padre_id', false) // Cuenta hoja
                            ->first();

        $this->assertNotNull($cuentaInventario, 'Debe existir una cuenta de inventario válida');
        $this->assertNotNull($cuentaCosto, 'Debe existir una cuenta de costo válida');

        // Test con datos válidos
        $response = $this->postJson('/productos', [
            'nombre' => 'Producto Test',
            'categoria' => 'Test',
            'unidad_medida' => 'PCS',
            'precio_compra' => 100.00,
            'precio_venta' => 150.00,
            'stock_minimo' => 10,
            'stock_maximo' => 100,
            'cuenta_inventario_id' => $cuentaInventario->id,
            'cuenta_costo_id' => $cuentaCosto->id,
            'activo' => true
        ]);

        $response->assertStatus(302); // Redirect after successful creation
    }

    /**
     * Test de formateo de opciones para Select2
     */
    public function test_formateo_para_select2()
    {
        $response = $this->getJson('/contabilidad/cuentas/select?contexto=inventario');
        $cuentas = $response->json();

        foreach ($cuentas as $cuenta) {
            // Verificar estructura esperada por Select2
            $this->assertArrayHasKey('id', $cuenta);
            $this->assertArrayHasKey('text', $cuenta);
            $this->assertArrayHasKey('disabled', $cuenta);
            
            // Verificar campos adicionales para lógica del frontend
            $this->assertArrayHasKey('es_hoja', $cuenta);
            $this->assertArrayHasKey('nivel', $cuenta);
            $this->assertArrayHasKey('codigo', $cuenta);
            $this->assertArrayHasKey('tipo', $cuenta);

            // Verificar que el texto contiene código y nombre
            $this->assertStringContainsString($cuenta['codigo'], $cuenta['text']);
        }
    }
}