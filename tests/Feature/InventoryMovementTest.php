<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Producto;
use App\Models\Bodega;
use App\Models\User;
use App\Models\Cuenta;
use App\Services\InventoryMovementService;
use App\DTOs\InventoryMovementResult;
use App\Exceptions\InventoryException;
use Illuminate\Support\Facades\DB;

class InventoryMovementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected InventoryMovementService $inventoryService;
    protected User $usuario;
    protected Bodega $bodega;
    protected Producto $producto;
    protected Cuenta $cuenta;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeders para tener datos de prueba
        $this->artisan('db:seed');
        
        $this->inventoryService = app(InventoryMovementService::class);
        
        // Obtener registros de prueba
        $this->usuario = User::first();
        $this->bodega = Bodega::first();
        $this->producto = Producto::first();
        $this->cuenta = Cuenta::where('tipo_cuenta', 'inventario')->first();
    }

    /** @test */
    public function puede_crear_movimiento_de_entrada()
    {
        $movementData = [
            'tipo_movimiento' => 'entrada',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Compra de mercancía para testing',
            'usuario_id' => $this->usuario->id,
            'bodega_id' => $this->bodega->id,
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 50,
                    'precio_unitario' => 15000.00,
                    'observaciones' => 'Entrada de prueba'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => $this->cuenta->id,
                'cuenta_credito' => Cuenta::where('codigo_cuenta', '2205')->first()->id ?? $this->cuenta->id,
                'concepto' => 'Compra de inventario - Test'
            ]
        ];

        $resultado = $this->inventoryService->createAndPost($movementData);

        $this->assertInstanceOf(InventoryMovementResult::class, $resultado);
        $this->assertTrue($resultado->success);
        $this->assertNotNull($resultado->movimiento);
        $this->assertNotNull($resultado->asiento);
        $this->assertEquals('entrada', $resultado->movimiento->tipo_movimiento);
        $this->assertEquals(50, $resultado->movimiento->detalles->first()->cantidad);
    }

    /** @test */
    public function puede_crear_movimiento_de_salida()
    {
        // Primero crear una entrada para tener existencias
        $entradaData = [
            'tipo_movimiento' => 'entrada',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Entrada inicial para testing',
            'usuario_id' => $this->usuario->id,
            'bodega_id' => $this->bodega->id,
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 100,
                    'precio_unitario' => 15000.00,
                    'observaciones' => 'Entrada inicial'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => $this->cuenta->id,
                'cuenta_credito' => Cuenta::where('codigo_cuenta', '2205')->first()->id ?? $this->cuenta->id,
                'concepto' => 'Compra inicial - Test'
            ]
        ];

        $this->inventoryService->createAndPost($entradaData);

        // Ahora crear la salida
        $salidaData = [
            'tipo_movimiento' => 'salida',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Venta de mercancía para testing',
            'usuario_id' => $this->usuario->id,
            'bodega_id' => $this->bodega->id,
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 30,
                    'precio_unitario' => 20000.00,
                    'observaciones' => 'Venta de prueba'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => Cuenta::where('codigo_cuenta', '1305')->first()->id ?? $this->cuenta->id,
                'cuenta_credito' => $this->cuenta->id,
                'concepto' => 'Venta de inventario - Test'
            ]
        ];

        $resultado = $this->inventoryService->createAndPost($salidaData);

        $this->assertInstanceOf(InventoryMovementResult::class, $resultado);
        $this->assertTrue($resultado->success);
        $this->assertEquals('salida', $resultado->movimiento->tipo_movimiento);
        $this->assertEquals(30, $resultado->movimiento->detalles->first()->cantidad);
    }

    /** @test */
    public function falla_cuando_no_hay_existencias_suficientes()
    {
        $this->expectException(InventoryException::class);
        $this->expectExceptionMessage('Existencias insuficientes');

        $movementData = [
            'tipo_movimiento' => 'salida',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Intento de salida sin existencias',
            'usuario_id' => $this->usuario->id,
            'bodega_id' => $this->bodega->id,
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 999999,
                    'precio_unitario' => 20000.00,
                    'observaciones' => 'Salida imposible'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => Cuenta::where('codigo_cuenta', '1305')->first()->id ?? $this->cuenta->id,
                'cuenta_credito' => $this->cuenta->id,
                'concepto' => 'Intento de venta sin stock'
            ]
        ];

        $this->inventoryService->createAndPost($movementData);
    }

    /** @test */
    public function puede_crear_movimiento_de_ajuste()
    {
        $movementData = [
            'tipo_movimiento' => 'ajuste',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Ajuste de inventario por conteo físico',
            'usuario_id' => $this->usuario->id,
            'bodega_id' => $this->bodega->id,
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 10,
                    'precio_unitario' => 15000.00,
                    'observaciones' => 'Ajuste por diferencia en conteo'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => $this->cuenta->id,
                'cuenta_credito' => Cuenta::where('codigo_cuenta', '5395')->first()->id ?? $this->cuenta->id,
                'concepto' => 'Ajuste de inventario - Test'
            ]
        ];

        $resultado = $this->inventoryService->createAndPost($movementData);

        $this->assertInstanceOf(InventoryMovementResult::class, $resultado);
        $this->assertTrue($resultado->success);
        $this->assertEquals('ajuste', $resultado->movimiento->tipo_movimiento);
    }

    /** @test */
    public function puede_crear_movimiento_de_transferencia()
    {
        $bodegaDestino = Bodega::where('id', '!=', $this->bodega->id)->first();
        
        // Primero crear existencias en bodega origen
        $entradaData = [
            'tipo_movimiento' => 'entrada',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Entrada para transferencia',
            'usuario_id' => $this->usuario->id,
            'bodega_id' => $this->bodega->id,
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 50,
                    'precio_unitario' => 15000.00,
                    'observaciones' => 'Entrada inicial'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => $this->cuenta->id,
                'cuenta_credito' => Cuenta::where('codigo_cuenta', '2205')->first()->id ?? $this->cuenta->id,
                'concepto' => 'Entrada para transferencia'
            ]
        ];

        $this->inventoryService->createAndPost($entradaData);

        // Ahora hacer la transferencia
        $transferData = [
            'tipo_movimiento' => 'transferencia',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Transferencia entre bodegas',
            'usuario_id' => $this->usuario->id,
            'bodega_id' => $this->bodega->id,
            'bodega_destino_id' => $bodegaDestino->id,
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 20,
                    'precio_unitario' => 15000.00,
                    'observaciones' => 'Transferencia de prueba'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => $this->cuenta->id,
                'cuenta_credito' => $this->cuenta->id,
                'concepto' => 'Transferencia de inventario - Test'
            ]
        ];

        $resultado = $this->inventoryService->createAndPost($transferData);

        $this->assertInstanceOf(InventoryMovementResult::class, $resultado);
        $this->assertTrue($resultado->success);
        $this->assertEquals('transferencia', $resultado->movimiento->tipo_movimiento);
        $this->assertEquals($bodegaDestino->id, $resultado->movimiento->bodega_destino_id);
    }

    /** @test */
    public function valida_datos_requeridos()
    {
        $this->expectException(InventoryException::class);
        $this->expectExceptionMessage('Faltan datos requeridos');

        $movementData = [
            'tipo_movimiento' => 'entrada',
            // Faltan campos requeridos
        ];

        $this->inventoryService->createAndPost($movementData);
    }

    /** @test */
    public function rollback_en_caso_de_error_de_stored_procedure()
    {
        // Simular un error forzando datos inválidos que harán fallar el SP
        $movementData = [
            'tipo_movimiento' => 'entrada',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Test de rollback',
            'usuario_id' => 99999, // ID inexistente
            'bodega_id' => $this->bodega->id,
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 50,
                    'precio_unitario' => 15000.00,
                    'observaciones' => 'Test rollback'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => $this->cuenta->id,
                'cuenta_credito' => $this->cuenta->id,
                'concepto' => 'Test rollback'
            ]
        ];

        try {
            $this->inventoryService->createAndPost($movementData);
            $this->fail('Se esperaba que fallara por usuario inexistente');
        } catch (InventoryException $e) {
            // Verificar que no se creó ningún registro
            $this->assertEquals(0, DB::table('movimientos_inventario')
                ->where('observaciones', 'Test de rollback')
                ->count());
        }
    }
}
