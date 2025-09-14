<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            // === PRODUCTOS ALIMENTICIOS ===
            
            // Productos Frescos
            [
                'sku' => 'ALI-FRE-001',
                'nombre' => 'Manzana Red Delicious',
                'descripcion' => 'Manzanas rojas frescas, calibre grande, origen nacional',
                                'unidad_id' => 1,  // UND
                'categoria_id' => 2, // Productos Frescos
                'precio_compra_promedio' => 1200.00,
                'precio_venta' => 1800.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,  // 110101 - Inventario de Productos Terminados
                'cuenta_costo_id' => 33,    // 510101 - Costo de Productos Vendidos
                'cuenta_contraparte_id' => 43, // 610201 - Comisiones de Ventas
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'sku' => 'ALI-FRE-002',
                'nombre' => 'Banano Premium',
                'descripcion' => 'Bananos maduros punto perfecto, primera calidad',
                'unidad_id' => 1,  // UND
                'categoria_id' => 2, // Productos Frescos
                'precio_compra_promedio' => 800.00,
                'precio_venta' => 1200.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],
            [
                'sku' => 'ALI-FRE-003',
                'nombre' => 'Lechuga Batavia',
                'descripcion' => 'Lechuga fresca hidropónica, empaque individual',
                'unidad_id' => 1,  // UND
                'categoria_id' => 2, // Productos Frescos
                'precio_compra_promedio' => 2500.00,
                'precio_venta' => 3500.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],

            // Productos Lácteos
            [
                'sku' => 'ALI-LAC-001',
                'nombre' => 'Leche Entera UHT 1L',
                'descripcion' => 'Leche entera ultra pasteurizada, empaque tetra pak 1 litro',
                'unidad_id' => 1,  // UND
                'categoria_id' => 5, // Productos Lácteos
                'precio_compra_promedio' => 3200.00,
                'precio_venta' => 4500.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],
            [
                'sku' => 'ALI-LAC-002',
                'nombre' => 'Queso Mozarella 500g',
                'descripcion' => 'Queso mozarella fresco, empaque al vacío 500 gramos',
                'unidad_id' => 1,  // UND
                'categoria_id' => 5, // Productos Lácteos
                'precio_compra_promedio' => 8500.00,
                'precio_venta' => 12000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],

            // === PRODUCTOS TECNOLÓGICOS ===
            [
                'sku' => 'TEC-COM-001',
                'nombre' => 'Laptop Dell Inspiron 15',
                'descripcion' => 'Laptop Dell Inspiron 15, Intel i5, 8GB RAM, 256GB SSD, Windows 11',
                'unidad_id' => 1,  // UND
                'categoria_id' => 13, // Equipos de Computación
                'precio_compra_promedio' => 2800000.00,
                'precio_venta' => 3500000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 24, // Proveedores Internacionales
                'created_by' => 1
            ],
            [
                'sku' => 'TEC-MOV-001',
                'nombre' => 'Smartphone Samsung Galaxy A54',
                'descripcion' => 'Samsung Galaxy A54 5G, 128GB, Cámara 50MP, Pantalla 6.4"',
                'unidad_id' => 1,  // UND
                'categoria_id' => 14, // Dispositivos Móviles
                'precio_compra_promedio' => 1200000.00,
                'precio_venta' => 1650000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 24,
                'created_by' => 1
            ],
            [
                'sku' => 'TEC-AUD-001',
                'nombre' => 'Audífonos Sony WH-1000XM4',
                'descripcion' => 'Audífonos inalámbricos con cancelación de ruido, 30h batería',
                'unidad_id' => 1,  // UND
                'categoria_id' => 15, // Audio y Video
                'precio_compra_promedio' => 850000.00,
                'precio_venta' => 1200000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 24,
                'created_by' => 1
            ],

            // === PRODUCTOS FARMACÉUTICOS ===
            [
                'sku' => 'FAR-MED-001',
                'nombre' => 'Acetaminofén 500mg x 20 tab',
                'descripcion' => 'Acetaminofén 500mg, caja por 20 tabletas, analgésico antipirético',
                'unidad_id' => 1,  // UND
                'categoria_id' => 8, // Medicamentos
                'precio_compra_promedio' => 3500.00,
                'precio_venta' => 5200.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],
            [
                'sku' => 'FAR-VIT-001',
                'nombre' => 'Vitamina C 1000mg x 30 cap',
                'descripcion' => 'Vitamina C 1000mg, frasco por 30 cápsulas, suplemento nutricional',
                'unidad_id' => 1,  // UND
                'categoria_id' => 9, // Vitaminas y Suplementos
                'precio_compra_promedio' => 18000.00,
                'precio_venta' => 25000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],

            // === PRODUCTOS TEXTILES ===
            [
                'sku' => 'TEX-ROH-001',
                'nombre' => 'Camisa Ejecutiva Blanca Talla M',
                'descripcion' => 'Camisa ejecutiva manga larga, color blanco, talla M, 100% algodón',
                'unidad_id' => 1,  // UND
                'categoria_id' => 18, // Ropa Hombre
                'precio_compra_promedio' => 45000.00,
                'precio_venta' => 75000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],
            [
                'sku' => 'TEX-CAL-001',
                'nombre' => 'Zapatos Formales Negros Talla 42',
                'descripcion' => 'Zapatos formales ejecutivos, cuero genuino, color negro, talla 42',
                'unidad_id' => 30,  // PAR
                'categoria_id' => 21, // Calzado
                'precio_compra_promedio' => 120000.00,
                'precio_venta' => 180000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],

            // === PRODUCTOS PARA EL HOGAR ===
            [
                'sku' => 'HOG-LIM-001',
                'nombre' => 'Detergente Líquido 3L',
                'descripcion' => 'Detergente líquido concentrado, fragancia lavanda, 3 litros',
                'unidad_id' => 1,  // UND
                'categoria_id' => 24, // Productos de Limpieza
                'precio_compra_promedio' => 15000.00,
                'precio_venta' => 22000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],
            [
                'sku' => 'HOG-COC-001',
                'nombre' => 'Set Ollas Acero Inoxidable 7 piezas',
                'descripcion' => 'Juego de ollas acero inoxidable, 7 piezas, fondo térmico',
                'unidad_id' => 29,  // JGO (Juego)
                'categoria_id' => 23, // Productos de Cocina
                'precio_compra_promedio' => 180000.00,
                'precio_venta' => 270000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],

            // === PRODUCTOS INDUSTRIALES ===
            [
                'sku' => 'IND-HER-001',
                'nombre' => 'Taladro Inalámbrico 18V',
                'descripcion' => 'Taladro inalámbrico 18V, incluye batería y cargador, 13mm chuck',
                'unidad_id' => 1,  // UND
                'categoria_id' => 28, // Herramientas
                'precio_compra_promedio' => 220000.00,
                'precio_venta' => 320000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],
            [
                'sku' => 'IND-SEG-001',
                'nombre' => 'Casco de Seguridad Blanco',
                'descripcion' => 'Casco de seguridad industrial, color blanco, ajuste de suspensión',
                'unidad_id' => 1,  // UND
                'categoria_id' => 32, // Seguridad Industrial
                'precio_compra_promedio' => 25000.00,
                'precio_venta' => 35000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],

            // === PRODUCTOS AUTOMOTRICES ===
            [
                'sku' => 'AUT-REP-001',
                'nombre' => 'Filtro de Aceite Universal',
                'descripcion' => 'Filtro de aceite motor, compatible múltiples marcas, rosca 3/4"',
                'unidad_id' => 1,  // UND
                'categoria_id' => 34, // Repuestos
                'precio_compra_promedio' => 12000.00,
                'precio_venta' => 18000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],
            [
                'sku' => 'AUT-LUB-001',
                'nombre' => 'Aceite Motor 15W40 4L',
                'descripcion' => 'Aceite multigrado para motor 15W40, galón 4 litros, mineral',
                'unidad_id' => 1,  // UND
                'categoria_id' => 36, // Lubricantes
                'precio_compra_promedio' => 45000.00,
                'precio_venta' => 65000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],

            // === PRODUCTOS DE OFICINA ===
            [
                'sku' => 'OFI-PAP-001',
                'nombre' => 'Papel Bond A4 75g Resma',
                'descripcion' => 'Papel bond blanco A4, gramaje 75g, resma 500 hojas',
                'unidad_id' => 1,  // UND
                'categoria_id' => 38, // Papelería
                'precio_compra_promedio' => 12000.00,
                'precio_venta' => 16000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],
            [
                'sku' => 'OFI-ESC-001',
                'nombre' => 'Bolígrafo Azul Caja x 12',
                'descripcion' => 'Bolígrafos tinta azul, punta media, caja por 12 unidades',
                'unidad_id' => 21,  // CAJ (Caja)
                'categoria_id' => 39, // Útiles de Escritura
                'precio_compra_promedio' => 18000.00,
                'precio_venta' => 24000.00,
                'activo' => true,
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ],

            // === PRODUCTOS CON MANEJO ESPECIAL ===
            
            // Producto que permite negativos (servicio)
            [
                'sku' => 'SER-TEC-001',
                'nombre' => 'Hora de Soporte Técnico',
                'descripcion' => 'Servicio de soporte técnico especializado por hora',
                'unidad_id' => 35,  // HR (Hora)
                'categoria_id' => 44, // Servicios Técnicos
                'precio_compra_promedio' => 0.00,
                'precio_venta' => 50000.00,
                'activo' => true,
                'permite_negativo' => true, // Los servicios pueden venderse sin stock
                'cuenta_inventario_id' => null, // Los servicios no manejan inventario
                'cuenta_costo_id' => 42,      // 5102 - Costo de Ventas - Servicios
                'cuenta_contraparte_id' => null,
                'created_by' => 1
            ],

            // Producto inactivo como ejemplo
            [
                'sku' => 'DIS-PROD-001',
                'nombre' => 'Producto Descontinuado',
                'descripcion' => 'Producto que ya no se maneja, solo para referencia histórica',
                'unidad_id' => 1,  // UND
                'categoria_id' => 2, // Productos Frescos
                'precio_compra_promedio' => 5000.00,
                'precio_venta' => 8000.00,
                'activo' => false, // Producto inactivo
                'permite_negativo' => false,
                'cuenta_inventario_id' => 7,
                'cuenta_costo_id' => 41,
                'cuenta_contraparte_id' => 23,
                'created_by' => 1
            ]
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }

        $this->command->info('✅ Productos demo creados exitosamente: ' . count($productos) . ' productos.');
    }
}
