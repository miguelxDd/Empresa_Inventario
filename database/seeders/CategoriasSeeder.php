<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            // Categorías principales por industria
            
            // === PRODUCTOS ALIMENTICIOS ===
            ['codigo' => 'ALI', 'nombre' => 'Productos Alimenticios', 'descripcion' => 'Todos los productos comestibles y bebidas', 'activa' => true],
            ['codigo' => 'ALI-FRE', 'nombre' => 'Productos Frescos', 'descripcion' => 'Frutas, verduras y productos perecederos', 'activa' => true],
            ['codigo' => 'ALI-CON', 'nombre' => 'Productos Conservados', 'descripcion' => 'Enlatados, congelados y preservados', 'activa' => true],
            ['codigo' => 'ALI-BEB', 'nombre' => 'Bebidas', 'descripcion' => 'Bebidas alcohólicas y no alcohólicas', 'activa' => true],
            ['codigo' => 'ALI-LAC', 'nombre' => 'Productos Lácteos', 'descripcion' => 'Leche, quesos, yogurts y derivados', 'activa' => true],
            ['codigo' => 'ALI-CAR', 'nombre' => 'Productos Cárnicos', 'descripcion' => 'Carnes, embutidos y productos del mar', 'activa' => true],
            
            // === PRODUCTOS FARMACÉUTICOS ===
            ['codigo' => 'FAR', 'nombre' => 'Productos Farmacéuticos', 'descripcion' => 'Medicamentos y productos de salud', 'activa' => true],
            ['codigo' => 'FAR-MED', 'nombre' => 'Medicamentos', 'descripcion' => 'Medicamentos con y sin prescripción', 'activa' => true],
            ['codigo' => 'FAR-VIT', 'nombre' => 'Vitaminas y Suplementos', 'descripcion' => 'Suplementos nutricionales y vitaminas', 'activa' => true],
            ['codigo' => 'FAR-COD', 'nombre' => 'Cuidado Dental', 'descripcion' => 'Productos de higiene dental', 'activa' => true],
            ['codigo' => 'FAR-DER', 'nombre' => 'Dermatológicos', 'descripcion' => 'Productos para el cuidado de la piel', 'activa' => true],
            
            // === PRODUCTOS TECNOLÓGICOS ===
            ['codigo' => 'TEC', 'nombre' => 'Productos Tecnológicos', 'descripcion' => 'Equipos electrónicos y tecnología', 'activa' => true],
            ['codigo' => 'TEC-COM', 'nombre' => 'Equipos de Computación', 'descripcion' => 'Computadoras, laptops y accesorios', 'activa' => true],
            ['codigo' => 'TEC-MOV', 'nombre' => 'Dispositivos Móviles', 'descripcion' => 'Teléfonos, tablets y accesorios', 'activa' => true],
            ['codigo' => 'TEC-AUD', 'nombre' => 'Audio y Video', 'descripcion' => 'Equipos de sonido, TV y multimedia', 'activa' => true],
            ['codigo' => 'TEC-GAM', 'nombre' => 'Gaming', 'descripcion' => 'Consolas, videojuegos y accesorios', 'activa' => true],
            
            // === PRODUCTOS TEXTILES ===
            ['codigo' => 'TEX', 'nombre' => 'Productos Textiles', 'descripcion' => 'Ropa, calzado y textiles', 'activa' => true],
            ['codigo' => 'TEX-ROH', 'nombre' => 'Ropa Hombre', 'descripcion' => 'Vestuario masculino', 'activa' => true],
            ['codigo' => 'TEX-ROM', 'nombre' => 'Ropa Mujer', 'descripcion' => 'Vestuario femenino', 'activa' => true],
            ['codigo' => 'TEX-RON', 'nombre' => 'Ropa Niños', 'descripcion' => 'Vestuario infantil', 'activa' => true],
            ['codigo' => 'TEX-CAL', 'nombre' => 'Calzado', 'descripcion' => 'Zapatos, botas y sandalias', 'activa' => true],
            ['codigo' => 'TEX-ACC', 'nombre' => 'Accesorios', 'descripcion' => 'Carteras, cinturones y accesorios', 'activa' => true],
            
            // === PRODUCTOS PARA EL HOGAR ===
            ['codigo' => 'HOG', 'nombre' => 'Productos para el Hogar', 'descripcion' => 'Artículos domésticos y del hogar', 'activa' => true],
            ['codigo' => 'HOG-COC', 'nombre' => 'Productos de Cocina', 'descripcion' => 'Utensilios y electrodomésticos de cocina', 'activa' => true],
            ['codigo' => 'HOG-LIM', 'nombre' => 'Productos de Limpieza', 'descripcion' => 'Detergentes, desinfectantes y limpiadores', 'activa' => true],
            ['codigo' => 'HOG-BAÑ', 'nombre' => 'Productos de Baño', 'descripcion' => 'Artículos de higiene personal y baño', 'activa' => true],
            ['codigo' => 'HOG-DEC', 'nombre' => 'Decoración', 'descripcion' => 'Artículos decorativos y ornamentales', 'activa' => true],
            ['codigo' => 'HOG-JAR', 'nombre' => 'Jardín y Exterior', 'descripcion' => 'Plantas, herramientas de jardín y exterior', 'activa' => true],
            
            // === PRODUCTOS INDUSTRIALES ===
            ['codigo' => 'IND', 'nombre' => 'Productos Industriales', 'descripcion' => 'Materiales y equipos industriales', 'activa' => true],
            ['codigo' => 'IND-HER', 'nombre' => 'Herramientas', 'descripcion' => 'Herramientas manuales y eléctricas', 'activa' => true],
            ['codigo' => 'IND-MAQ', 'nombre' => 'Maquinaria', 'descripcion' => 'Equipos y maquinaria industrial', 'activa' => true],
            ['codigo' => 'IND-MAT', 'nombre' => 'Materiales de Construcción', 'descripcion' => 'Cemento, acero, madera y materiales', 'activa' => true],
            ['codigo' => 'IND-QUI', 'nombre' => 'Productos Químicos', 'descripcion' => 'Químicos industriales y especializados', 'activa' => true],
            ['codigo' => 'IND-SEG', 'nombre' => 'Seguridad Industrial', 'descripcion' => 'Equipos de protección personal', 'activa' => true],
            
            // === PRODUCTOS AUTOMOTRICES ===
            ['codigo' => 'AUT', 'nombre' => 'Productos Automotrices', 'descripcion' => 'Repuestos y accesorios automotrices', 'activa' => true],
            ['codigo' => 'AUT-REP', 'nombre' => 'Repuestos', 'descripcion' => 'Repuestos originales y genéricos', 'activa' => true],
            ['codigo' => 'AUT-ACE', 'nombre' => 'Accesorios', 'descripcion' => 'Accesorios y complementos', 'activa' => true],
            ['codigo' => 'AUT-LLA', 'nombre' => 'Llantas y Neumáticos', 'descripcion' => 'Llantas, neumáticos y rines', 'activa' => true],
            ['codigo' => 'AUT-LUB', 'nombre' => 'Lubricantes', 'descripcion' => 'Aceites, grasas y lubricantes', 'activa' => true],
            
            // === PRODUCTOS DE OFICINA ===
            ['codigo' => 'OFI', 'nombre' => 'Productos de Oficina', 'descripcion' => 'Suministros y equipos de oficina', 'activa' => true],
            ['codigo' => 'OFI-PAP', 'nombre' => 'Papelería', 'descripcion' => 'Papel, cuadernos y suministros', 'activa' => true],
            ['codigo' => 'OFI-ESC', 'nombre' => 'Útiles de Escritura', 'descripcion' => 'Bolígrafos, lápices y marcadores', 'activa' => true],
            ['codigo' => 'OFI-EQU', 'nombre' => 'Equipos de Oficina', 'descripcion' => 'Impresoras, copiadoras y equipos', 'activa' => true],
            ['codigo' => 'OFI-MOB', 'nombre' => 'Mobiliario', 'descripcion' => 'Escritorios, sillas y mobiliario', 'activa' => true],
            
            // === SERVICIOS ===
            ['codigo' => 'SER', 'nombre' => 'Servicios', 'descripcion' => 'Servicios profesionales y técnicos', 'activa' => true],
            ['codigo' => 'SER-CON', 'nombre' => 'Consultoría', 'descripcion' => 'Servicios de consultoría especializada', 'activa' => true],
            ['codigo' => 'SER-MAN', 'nombre' => 'Mantenimiento', 'descripcion' => 'Servicios de mantenimiento y reparación', 'activa' => true],
            ['codigo' => 'SER-TEC', 'nombre' => 'Servicios Técnicos', 'descripcion' => 'Soporte técnico especializado', 'activa' => true],
            ['codigo' => 'SER-CAP', 'nombre' => 'Capacitación', 'descripcion' => 'Cursos y programas de capacitación', 'activa' => true]
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }

        $this->command->info('✅ Categorías creadas exitosamente: ' . count($categorias) . ' categorías.');
    }
}
