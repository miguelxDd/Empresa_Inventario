<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use App\Models\Producto;
use App\Models\Bodega;
use App\Models\User;
use App\Models\Cuenta;

class InventoryApiTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeders para tener datos de prueba
        $this->artisan('db:seed');
    }

    /** @test */
    public function puede_obtener_opciones_de_configuracion()
    {
        $response = $this->getJson('/api/movimientos/config/options');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'tipos_movimiento',
                    'bodegas' => [
                        '*' => ['id', 'nombre', 'codigo']
                    ],
                    'productos' => [
                        '*' => ['id', 'nombre', 'codigo', 'categoria', 'unidad_medida']
                    ],
                    'cuentas_contables' => [
                        '*' => ['id', 'codigo_cuenta', 'nombre_cuenta', 'tipo_cuenta']
                    ],
                    'usuarios' => [
                        '*' => ['id', 'name', 'email']
                    ]
                ]);
    }

    /** @test */
    public function puede_obtener_ejemplos_de_movimientos()
    {
        $response = $this->getJson('/api/movimientos/config/examples');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'entrada' => [
                        'tipo_movimiento',
                        'fecha_movimiento',
                        'observaciones',
                        'usuario_id',
                        'bodega_id',
                        'detalles' => [
                            '*' => [
                                'producto_id',
                                'cantidad',
                                'precio_unitario',
                                'observaciones'
                            ]
                        ],
                        'asiento_contable' => [
                            'cuenta_debito',
                            'cuenta_credito',
                            'concepto'
                        ]
                    ],
                    'salida',
                    'transferencia',
                    'ajuste'
                ]);
    }

    /** @test */
    public function puede_crear_movimiento_de_entrada_via_api()
    {
        $usuario = User::first();
        $bodega = Bodega::first();
        $producto = Producto::first();
        $cuentaInventario = Cuenta::where('tipo_cuenta', 'inventario')->first();
        $cuentaProveedores = Cuenta::where('codigo_cuenta', '2205')->first() ?? $cuentaInventario;

        $data = [
            'tipo_movimiento' => 'entrada',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Compra de mercancía via API',
            'usuario_id' => $usuario->id,
            'bodega_id' => $bodega->id,
            'detalles' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 25,
                    'precio_unitario' => 12000.00,
                    'observaciones' => 'Entrada via API test'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => $cuentaInventario->id,
                'cuenta_credito' => $cuentaProveedores->id,
                'concepto' => 'Compra de inventario via API'
            ]
        ];

        $response = $this->postJson('/api/movimientos', $data);

        $response->assertStatus(Response::HTTP_CREATED)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'movimiento' => [
                            'id',
                            'numero',
                            'tipo_movimiento',
                            'fecha_movimiento',
                            'estado',
                            'valor_total'
                        ],
                        'asiento' => [
                            'id',
                            'numero',
                            'fecha',
                            'estado',
                            'total_debe',
                            'total_haber'
                        ],
                        'detalles',
                        'existencias_afectadas',
                        'resumen'
                    ],
                    'message'
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'movimiento' => [
                            'tipo_movimiento' => 'entrada'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function puede_consultar_movimiento_por_id()
    {
        // Primero crear un movimiento
        $usuario = User::first();
        $bodega = Bodega::first();
        $producto = Producto::first();
        $cuentaInventario = Cuenta::where('tipo_cuenta', 'inventario')->first();

        $data = [
            'tipo_movimiento' => 'entrada',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Movimiento para consulta',
            'usuario_id' => $usuario->id,
            'bodega_id' => $bodega->id,
            'detalles' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 15,
                    'precio_unitario' => 10000.00,
                    'observaciones' => 'Para consulta test'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => $cuentaInventario->id,
                'cuenta_credito' => $cuentaInventario->id,
                'concepto' => 'Movimiento para consulta'
            ]
        ];

        $createResponse = $this->postJson('/api/movimientos', $data);
        $movimientoId = $createResponse->json('data.movimiento.id');

        // Ahora consultar el movimiento creado
        $response = $this->getJson("/api/movimientos/{$movimientoId}");

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'movimiento',
                        'asiento',
                        'detalles',
                        'existencias_afectadas',
                        'resumen'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'movimiento' => [
                            'id' => $movimientoId,
                            'tipo_movimiento' => 'entrada'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function retorna_error_para_movimiento_inexistente()
    {
        $response = $this->getJson('/api/movimientos/99999');

        $response->assertStatus(Response::HTTP_NOT_FOUND)
                ->assertJson([
                    'success' => false,
                    'message' => 'Movimiento no encontrado'
                ]);
    }

    /** @test */
    public function valida_datos_requeridos_en_creacion()
    {
        $data = [
            'tipo_movimiento' => 'entrada',
            // Faltan campos requeridos
        ];

        $response = $this->postJson('/api/movimientos', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ])
                ->assertJson([
                    'success' => false
                ]);
    }

    /** @test */
    public function puede_cancelar_movimiento()
    {
        // Primero crear un movimiento
        $usuario = User::first();
        $bodega = Bodega::first();
        $producto = Producto::first();
        $cuentaInventario = Cuenta::where('tipo_cuenta', 'inventario')->first();

        $data = [
            'tipo_movimiento' => 'entrada',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Movimiento para cancelar',
            'usuario_id' => $usuario->id,
            'bodega_id' => $bodega->id,
            'detalles' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 10,
                    'precio_unitario' => 8000.00,
                    'observaciones' => 'Para cancelación test'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => $cuentaInventario->id,
                'cuenta_credito' => $cuentaInventario->id,
                'concepto' => 'Movimiento para cancelar'
            ]
        ];

        $createResponse = $this->postJson('/api/movimientos', $data);
        $movimientoId = $createResponse->json('data.movimiento.id');

        // Ahora cancelar el movimiento
        $response = $this->patchJson("/api/movimientos/{$movimientoId}/cancel", [
            'motivo_cancelacion' => 'Cancelación de prueba'
        ]);

        $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'success' => true,
                    'message' => 'Movimiento cancelado exitosamente'
                ]);
    }

    /** @test */
    public function no_puede_cancelar_movimiento_ya_cancelado()
    {
        // Crear y cancelar un movimiento
        $usuario = User::first();
        $bodega = Bodega::first();
        $producto = Producto::first();
        $cuentaInventario = Cuenta::where('tipo_cuenta', 'inventario')->first();

        $data = [
            'tipo_movimiento' => 'entrada',
            'fecha_movimiento' => now()->format('Y-m-d'),
            'observaciones' => 'Movimiento para doble cancelación',
            'usuario_id' => $usuario->id,
            'bodega_id' => $bodega->id,
            'detalles' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 5,
                    'precio_unitario' => 6000.00,
                    'observaciones' => 'Para doble cancelación test'
                ]
            ],
            'asiento_contable' => [
                'cuenta_debito' => $cuentaInventario->id,
                'cuenta_credito' => $cuentaInventario->id,
                'concepto' => 'Movimiento para doble cancelación'
            ]
        ];

        $createResponse = $this->postJson('/api/movimientos', $data);
        $movimientoId = $createResponse->json('data.movimiento.id');

        // Primera cancelación
        $this->patchJson("/api/movimientos/{$movimientoId}/cancel", [
            'motivo_cancelacion' => 'Primera cancelación'
        ]);

        // Intentar cancelar de nuevo
        $response = $this->patchJson("/api/movimientos/{$movimientoId}/cancel", [
            'motivo_cancelacion' => 'Segunda cancelación'
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJson([
                    'success' => false,
                    'message' => 'El movimiento ya está cancelado'
                ]);
    }
}
