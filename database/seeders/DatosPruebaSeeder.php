<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bodega;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Unidade;

class DatosPruebaSeeder extends Seeder
{
    public function run()
    {
        // Crear usuario de prueba si no existe
        $user = \App\Models\User::firstOrCreate([
            'email' => 'admin@test.com'
        ], [
            'name' => 'Administrador',
            'password' => bcrypt('password')
        ]);

        // Asegurar que hay al menos 2 bodegas activas
        if (Bodega::where('activa', true)->count() < 2) {
            Bodega::updateOrCreate([
                'codigo' => 'BG001'
            ], [
                'nombre' => 'Bodega Principal',
                'direccion' => 'Dirección Principal',
                'responsable_id' => $user->id,
                'activa' => true
            ]);

            Bodega::updateOrCreate([
                'codigo' => 'BG002'
            ], [
                'nombre' => 'Bodega Secundaria',
                'direccion' => 'Dirección Secundaria', 
                'responsable_id' => $user->id,
                'activa' => true
            ]);
        }

        // Asegurar que hay categorías
        $categoria = Categoria::firstOrCreate([
            'codigo' => 'ELEC'
        ], [
            'nombre' => 'Electrónicos',
            'descripcion' => 'Productos electrónicos'
        ]);

        // Asegurar que hay unidades
        $unidad = Unidade::firstOrCreate([
            'codigo' => 'UND'
        ], [
            'nombre' => 'Unidad',
            'abreviatura' => 'UND'
        ]);

        // Crear algunos productos de prueba si no existen
        if (Producto::count() < 3) {
            Producto::firstOrCreate([
                'sku' => 'TEST-001'
            ], [
                'nombre' => 'Producto Test 1',
                'descripcion' => 'Producto de prueba para testing',
                'categoria_id' => $categoria->id,
                'unidad_id' => $unidad->id,
                'precio_venta' => 100.00,
                'activo' => true,
                'created_by' => $user->id
            ]);

            Producto::firstOrCreate([
                'sku' => 'TEST-002'
            ], [
                'nombre' => 'Producto Test 2',
                'descripcion' => 'Segundo producto de prueba',
                'categoria_id' => $categoria->id,
                'unidad_id' => $unidad->id,
                'precio_venta' => 200.00,
                'activo' => true,
                'created_by' => $user->id
            ]);
        }

        $this->command->info('Datos de prueba creados exitosamente.');
        $this->command->info('Bodegas activas: ' . Bodega::where('activa', true)->count());
        $this->command->info('Productos activos: ' . Producto::where('activo', true)->count());
    }
}
