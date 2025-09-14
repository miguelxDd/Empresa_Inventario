<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ParametrosInventario;

class ParametrosInventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parametros = [
            // === CONFIGURACIÓN DE COSTOS ===
            [
                'clave' => 'modo_costo',
                'valor' => 'promedio_movil',
                'descripcion' => 'Método de cálculo de costos: promedio_movil, fifo, lifo, promedio_ponderado'
            ],
            [
                'clave' => 'redondeo_costos',
                'valor' => '4',
                'descripcion' => 'Número de decimales para redondeo de costos'
            ],
            [
                'clave' => 'incluir_gastos_compra',
                'valor' => 'true',
                'descripcion' => 'Incluir gastos de compra (fletes, seguros) en el costo del producto'
            ],
            [
                'clave' => 'actualizar_costo_automatico',
                'valor' => 'true',
                'descripcion' => 'Actualizar automáticamente el costo promedio en cada compra'
            ],

            // === CONFIGURACIÓN DE EXISTENCIAS ===
            [
                'clave' => 'permitir_negativos',
                'valor' => 'false',
                'descripcion' => 'Permitir existencias negativas en el inventario'
            ],
            [
                'clave' => 'stock_minimo_global',
                'valor' => '10',
                'descripcion' => 'Stock mínimo por defecto para nuevos productos'
            ],
            [
                'clave' => 'stock_maximo_global',
                'valor' => '1000',
                'descripcion' => 'Stock máximo por defecto para nuevos productos'
            ],
            [
                'clave' => 'alerta_stock_minimo',
                'valor' => 'true',
                'descripcion' => 'Activar alertas cuando el stock esté por debajo del mínimo'
            ],

            // === CONFIGURACIÓN DE MOVIMIENTOS ===
            [
                'clave' => 'requiere_autorizacion_ajustes',
                'valor' => 'true',
                'descripcion' => 'Los ajustes de inventario requieren autorización especial'
            ],
            [
                'clave' => 'limite_ajuste_sin_autorizacion',
                'valor' => '1000.00',
                'descripcion' => 'Valor máximo de ajuste sin requerir autorización especial'
            ],
            [
                'clave' => 'generar_asiento_automatico',
                'valor' => 'true',
                'descripcion' => 'Generar asientos contables automáticamente para movimientos'
            ],
            [
                'clave' => 'permitir_anular_movimientos',
                'valor' => 'false',
                'descripcion' => 'Permitir anular movimientos de inventario ya procesados'
            ],

            // === CONFIGURACIÓN DE VALORIZACIÓN ===
            [
                'clave' => 'metodo_valorizacion',
                'valor' => 'costo_promedio',
                'descripcion' => 'Método de valorización: costo_promedio, ultimo_costo, costo_estandar'
            ],
            [
                'clave' => 'revaluar_inventario_mensual',
                'valor' => 'true',
                'descripcion' => 'Revaluar inventario automáticamente cada mes'
            ],
            [
                'clave' => 'moneda_base',
                'valor' => 'COP',
                'descripcion' => 'Moneda base para valorización (COP, USD, EUR)'
            ],

            // === CONFIGURACIÓN DE REPORTES ===
            [
                'clave' => 'frecuencia_reporte_stock',
                'valor' => 'diario',
                'descripcion' => 'Frecuencia de generación de reportes de stock: diario, semanal, mensual'
            ],
            [
                'clave' => 'incluir_productos_inactivos',
                'valor' => 'false',
                'descripcion' => 'Incluir productos inactivos en reportes por defecto'
            ],
            [
                'clave' => 'exportar_formato_excel',
                'valor' => 'true',
                'descripcion' => 'Habilitar exportación en formato Excel'
            ],

            // === CONFIGURACIÓN DE SEGURIDAD ===
            [
                'clave' => 'auditoria_movimientos',
                'valor' => 'true',
                'descripcion' => 'Registrar auditoría completa de todos los movimientos'
            ],
            [
                'clave' => 'backup_automatico',
                'valor' => 'true',
                'descripcion' => 'Realizar backup automático de datos de inventario'
            ],
            [
                'clave' => 'dias_retencion_auditoria',
                'valor' => '365',
                'descripcion' => 'Días de retención de registros de auditoría'
            ],

            // === CONFIGURACIÓN DE INTEGRACIÓN ===
            [
                'clave' => 'sincronizar_contabilidad',
                'valor' => 'true',
                'descripcion' => 'Sincronizar automáticamente con el módulo de contabilidad'
            ],
            [
                'clave' => 'sincronizar_ventas',
                'valor' => 'true',
                'descripcion' => 'Sincronizar automáticamente con el módulo de ventas'
            ],
            [
                'clave' => 'sincronizar_compras',
                'valor' => 'true',
                'descripcion' => 'Sincronizar automáticamente con el módulo de compras'
            ],

            // === CONFIGURACIÓN DE NOTIFICACIONES ===
            [
                'clave' => 'notificar_stock_minimo',
                'valor' => 'true',
                'descripcion' => 'Enviar notificaciones por stock mínimo'
            ],
            [
                'clave' => 'email_responsable_inventario',
                'valor' => 'inventario@empresa.com',
                'descripcion' => 'Email del responsable de inventario para notificaciones'
            ],

            // === CONFIGURACIÓN DE CÓDIGOS ===
            [
                'clave' => 'formato_codigo_producto',
                'valor' => 'PROD-{NNNNNN}',
                'descripcion' => 'Formato para códigos de productos automáticos'
            ],
            [
                'clave' => 'formato_codigo_movimiento',
                'valor' => 'MOV-{YYYY}-{MM}-{NNNNNN}',
                'descripcion' => 'Formato para códigos de movimientos automáticos'
            ]
        ];

        foreach ($parametros as $parametro) {
            ParametrosInventario::create($parametro);
        }

        $this->command->info('✅ Parámetros de inventario creados exitosamente: ' . count($parametros) . ' parámetros.');
    }
}
