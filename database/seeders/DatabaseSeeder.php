<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seed de la base de datos del sistema de inventario...');
        $this->command->newLine();

        // === SEEDERS OBLIGATORIOS EN ORDEN ===
        $this->command->info('📊 Creando plan de cuentas contables...');
        $this->call(CuentasSeeder::class);
        $this->command->newLine();

        $this->command->info('📏 Creando unidades de medida...');
        $this->call(UnidadesSeeder::class);
        $this->command->newLine();

        $this->command->info('📂 Creando categorías de productos...');
        $this->call(CategoriasSeeder::class);
        $this->command->newLine();

        $this->command->info('🏪 Creando bodegas de almacenamiento...');
        $this->call(BodegasSeeder::class);
        $this->command->newLine();

        $this->command->info('⚙️ Configurando parámetros del sistema de inventario...');
        $this->call(ParametrosInventarioSeeder::class);
        $this->command->newLine();

        $this->command->info('📋 Creando reglas contables por tipo de movimiento...');
        $this->call(ReglasContablesSeeder::class);
        $this->command->newLine();

        $this->command->info('📦 Creando productos de demostración...');
        $this->call(ProductosSeeder::class);
        $this->command->newLine();

        // === USUARIO DE PRUEBA ===
        $this->command->info('👤 Creando usuarios de prueba...');
        
        // Usuario administrador principal
        User::factory()->create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@empresa.com',
            'password' => bcrypt('admin123'), // En producción usar contraseña segura
        ]);

        // Usuario contador
        User::factory()->create([
            'name' => 'Contador Principal',
            'email' => 'contador@empresa.com',
            'password' => bcrypt('contador123'),
        ]);

        // Usuario bodeguero
        User::factory()->create([
            'name' => 'Jefe de Bodega',
            'email' => 'bodega@empresa.com',
            'password' => bcrypt('bodega123'),
        ]);

        // Usuario vendedor
        User::factory()->create([
            'name' => 'Vendedor Demo',
            'email' => 'ventas@empresa.com',
            'password' => bcrypt('ventas123'),
        ]);

        $this->command->newLine();
        $this->command->info('✅ ¡Seed completado exitosamente!');
        $this->command->newLine();
        
        // === RESUMEN DE LO CREADO ===
        $this->command->info('📈 RESUMEN DE DATOS CREADOS:');
        $this->command->table(
            ['Entidad', 'Cantidad', 'Descripción'],
            [
                ['Cuentas Contables', '50+', 'Plan completo de cuentas para inventario'],
                ['Unidades de Medida', '40+', 'Unidades básicas, comerciales e industriales'],
                ['Categorías', '45+', 'Categorías por industria con subcategorías'],
                ['Bodegas', '20+', 'Bodegas principales, especializadas y virtuales'],
                ['Parámetros', '35+', 'Configuración completa del sistema'],
                ['Reglas Contables', '25+', 'Reglas para todos los tipos de movimiento'],
                ['Productos Demo', '25+', 'Productos de ejemplo de diversas categorías'],
                ['Usuarios', '4', 'Admin, Contador, Bodeguero, Vendedor'],
            ]
        );
        
        $this->command->newLine();
        $this->command->info('🔑 CREDENCIALES DE ACCESO:');
        $this->command->table(
            ['Usuario', 'Email', 'Contraseña', 'Rol'],
            [
                ['Administrador', 'admin@empresa.com', 'admin123', 'Administrador General'],
                ['Contador', 'contador@empresa.com', 'contador123', 'Contabilidad e Informes'],
                ['Bodeguero', 'bodega@empresa.com', 'bodega123', 'Gestión de Inventario'],
                ['Vendedor', 'ventas@empresa.com', 'ventas123', 'Ventas y Consultas'],
            ]
        );
        
        $this->command->newLine();
        $this->command->warn('⚠️  IMPORTANTE: Cambiar todas las contraseñas en producción');
        $this->command->info('🎯 El sistema está listo para usar con datos de prueba completos');
    }
}
