<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use Illuminate\Http\Request;

class CuentaController extends Controller
{
    /**
     * Búsqueda de cuentas para autocomplete
     */
    public function search(Request $request)
    {
        $termino = $request->get('q', '');
        
        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        $cuentas = Cuenta::activas()
            ->buscar($termino)
            ->limit(20)
            ->get(['id', 'codigo', 'nombre'])
            ->map(function($cuenta) {
                return [
                    'id' => $cuenta->id,
                    'text' => $cuenta->codigo . ' - ' . $cuenta->nombre,
                    'codigo' => $cuenta->codigo,
                    'nombre' => $cuenta->nombre
                ];
            });

        return response()->json($cuentas);
    }
}
