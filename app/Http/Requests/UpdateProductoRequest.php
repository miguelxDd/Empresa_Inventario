<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $producto = $this->route('producto');
        
        return [
            'sku' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('productos', 'sku')->ignore($producto)
            ],
            'nombre' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'unidad_id' => ['required', 'integer', 'exists:unidades,id'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_venta' => ['nullable', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0'],
            'stock_maximo' => ['nullable', 'numeric', 'min:0'],
            'cuenta_inventario_id' => ['required', 'integer', 'exists:cuentas,id'],
            'cuenta_costo_id' => ['required', 'integer', 'exists:cuentas,id'],
            'cuenta_contraparte_id' => ['required', 'integer', 'exists:cuentas,id'],
            'activo' => ['boolean']
        ];
    }

    /**
     * Get the validation messages.
     */
    public function messages(): array
    {
        return [
            'sku.required' => 'El código SKU es obligatorio.',
            'sku.unique' => 'Este código SKU ya está en uso.',
            'sku.max' => 'El código SKU no puede exceder 50 caracteres.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 200 caracteres.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'unidad_id.required' => 'Debe seleccionar una unidad de medida.',
            'unidad_id.exists' => 'La unidad de medida seleccionada no existe.',
            'cuenta_inventario_id.required' => 'Debe seleccionar una cuenta de inventario.',
            'cuenta_inventario_id.exists' => 'La cuenta de inventario seleccionada no existe.',
            'cuenta_costo_id.required' => 'Debe seleccionar una cuenta de costo.',
            'cuenta_costo_id.exists' => 'La cuenta de costo seleccionada no existe.',
            'cuenta_contraparte_id.required' => 'Debe seleccionar una cuenta contraparte.',
            'cuenta_contraparte_id.exists' => 'La cuenta contraparte seleccionada no existe.',
            'precio_compra.min' => 'El precio de compra no puede ser negativo.',
            'precio_venta.min' => 'El precio de venta no puede ser negativo.',
            'stock_minimo.min' => 'El stock mínimo no puede ser negativo.',
            'stock_maximo.min' => 'El stock máximo no puede ser negativo.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->boolean('activo', false),
            'precio_compra' => $this->filled('precio_compra') ? $this->precio_compra : null,
            'precio_venta' => $this->filled('precio_venta') ? $this->precio_venta : null,
            'stock_minimo' => $this->filled('stock_minimo') ? $this->stock_minimo : null,
            'stock_maximo' => $this->filled('stock_maximo') ? $this->stock_maximo : null,
        ]);
    }
}
