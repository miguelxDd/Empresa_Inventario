<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Cuenta;

class ProductoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convertir el checkbox activo a boolean
        if ($this->has('activo')) {
            $this->merge([
                'activo' => $this->boolean('activo')
            ]);
        } else {
            $this->merge([
                'activo' => false
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:50|unique:productos,sku,' . $this->route('producto')?->id,
            'codigo_barras' => 'nullable|string|max:50',
            'unidad_id' => 'required|exists:unidades,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'stock_maximo' => 'required|integer|min:0',
            'ubicacion' => 'nullable|string|max:100',
            'proveedor' => 'nullable|string|max:255',
            'fecha_vencimiento' => 'nullable|date',
            'lote' => 'nullable|string|max:50',
            'notas' => 'nullable|string',
            'activo' => 'nullable|boolean',
            
            // Validación de cuentas contables
            'cuenta_inventario_id' => 'required|exists:cuentas,id',
            'cuenta_costo_id' => 'required|exists:cuentas,id', 
            'cuenta_contraparte_id' => 'nullable|exists:cuentas,id',
        ];

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'categoria_id.required' => 'La categoría es obligatoria.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'unidad_id.required' => 'La unidad de medida es obligatoria.',
            'unidad_id.exists' => 'La unidad de medida seleccionada no es válida.',
            'precio_compra.required' => 'El precio de compra es obligatorio.',
            'precio_compra.numeric' => 'El precio de compra debe ser un número.',
            'precio_compra.min' => 'El precio de compra debe ser mayor o igual a 0.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'precio_venta.min' => 'El precio de venta debe ser mayor o igual a 0.',
            'stock_minimo.required' => 'El stock mínimo es obligatorio.',
            'stock_minimo.integer' => 'El stock mínimo debe ser un número entero.',
            'stock_minimo.min' => 'El stock mínimo debe ser mayor o igual a 0.',
            'stock_maximo.required' => 'El stock máximo es obligatorio.',
            'stock_maximo.integer' => 'El stock máximo debe ser un número entero.',
            'stock_maximo.min' => 'El stock máximo debe ser mayor o igual a 0.',
            'cuenta_inventario_id.required' => 'La cuenta de inventario es obligatoria.',
            'cuenta_inventario_id.exists' => 'La cuenta de inventario seleccionada no es válida.',
            'cuenta_costo_id.required' => 'La cuenta de costo es obligatoria.',
            'cuenta_costo_id.exists' => 'La cuenta de costo seleccionada no es válida.',
            'cuenta_contraparte_id.exists' => 'La cuenta contraparte seleccionada no es válida.',
        ];
    }
}