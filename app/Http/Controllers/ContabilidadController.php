<?php

namespace App\Http\Controllers;

use App\Services\ContabilidadService;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ContabilidadController extends Controller
{
    private ContabilidadService $contabilidadService;

    public function __construct(ContabilidadService $contabilidadService)
    {
        $this->contabilidadService = $contabilidadService;
    }

    /**
     * Libro Mayor
     * GET /contabilidad/mayor?desde=YYYY-MM-DD&hasta=YYYY-MM-DD&cuentas[]=1&cuentas[]=2
     */
    public function libroMayor(Request $request): JsonResponse
    {
        $request->validate([
            'desde' => 'required|date|date_format:Y-m-d',
            'hasta' => 'required|date|date_format:Y-m-d|after_or_equal:desde',
            'cuentas' => 'sometimes|array',
            'cuentas.*' => 'integer|exists:cuentas,id'
        ]);

        try {
            $resultado = $this->contabilidadService->libroMayor(
                $request->desde,
                $request->hasta,
                $request->get('cuentas', [])
            );

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar libro mayor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Balance General
     * GET /contabilidad/balance-general?corte=YYYY-MM-DD
     */
    public function balanceGeneral(Request $request): JsonResponse
    {
        $request->validate([
            'corte' => 'required|date|date_format:Y-m-d'
        ]);

        try {
            $resultado = $this->contabilidadService->balanceGeneral($request->corte);

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar balance general: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Conciliación de Inventarios
     * GET /contabilidad/conciliacion-inventarios?corte=YYYY-MM-DD
     */
    public function conciliacionInventarios(Request $request): JsonResponse
    {
        $request->validate([
            'corte' => 'required|date|date_format:Y-m-d'
        ]);

        try {
            $resultado = $this->contabilidadService->conciliacionInventarios($request->corte);

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar conciliación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cuentas para selector jerárquico con filtros por contexto
     */
    public function cuentasSelect(Request $request)
    {
        $contexto = $request->get('contexto'); // inventario, costo, contraparte
        $query = $request->get('q', ''); // término de búsqueda
        
        // Cachear resultados por 10 minutos
        $cacheKey = "cuentas_select_{$contexto}_" . md5($query);
        
        return Cache::remember($cacheKey, 600, function() use ($contexto, $query) {
            $cuentasQuery = Cuenta::query()
                ->select([
                    'id',
                    'codigo',
                    'nombre',
                    'tipo',
                    'padre_id',
                    'activa'
                ])
                ->where('activa', true);

            // Aplicar filtros por contexto - SOLO las cuentas elegibles
            switch ($contexto) {
                case 'inventario':
                    // SOLO hojas exactas de inventarios: 110101, 110102, 110103, 110104
                    $cuentasQuery->whereIn('codigo', ['110101', '110102', '110103', '110104']);
                    break;
                    
                case 'costo':
                    // SOLO hojas exactas de costos: 5101, 5102, 5201, 5202
                    $cuentasQuery->whereIn('codigo', ['5101', '5102', '5201', '5202']);
                    break;
                    
                case 'contraparte':
                    // Cuentas específicas para contrapartes:
                    // Proveedores: 210101, 210102, 210103
                    // Bancos/Caja: 110201, 110202, 110203  
                    // Ajustes: 4901, 6101
                    $cuentasQuery->whereIn('codigo', [
                        // Proveedores (Pasivos)
                        '210101', '210102', '210103',
                        // Bancos y Caja (Activos)
                        '110201', '110202', '110203',
                        // Ajustes de inventario
                        '4901', '6101'
                    ]);
                    break;
                    
                default:
                    return response()->json(['error' => 'Contexto inválido'], 400);
            }

            // Aplicar búsqueda si se proporciona
            if (!empty($query)) {
                $cuentasQuery->where(function($q) use ($query) {
                    $q->where('codigo', 'LIKE', "%{$query}%")
                      ->orWhere('nombre', 'LIKE', "%{$query}%");
                });
            }

            $cuentas = $cuentasQuery->orderBy('codigo')->get();

            // Formatear para Select2 - sin jerarquía, solo las cuentas elegibles
            $result = $cuentas->map(function($cuenta) {
                return [
                    'id' => $cuenta->id,
                    'text' => $cuenta->codigo . ' - ' . $cuenta->nombre,
                    'disabled' => false, // Todas las que llegan aquí son seleccionables
                    'codigo' => $cuenta->codigo,
                    'tipo' => $cuenta->tipo,
                ];
            });

            return response()->json($result);
        });
    }
}
