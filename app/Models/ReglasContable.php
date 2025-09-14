<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ReglasContable
 * 
 * @property int $id
 * @property string $tipo_movimiento
 * @property int|null $categoria_producto_id
 * @property int|null $producto_id
 * @property int $cuenta_debe_id
 * @property int $cuenta_haber_id
 * @property int $prioridad
 * @property bool $activa
 * @property string|null $descripcion
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Categoria|null $categoria
 * @property Cuenta $cuenta
 * @property Producto|null $producto
 *
 * @package App\Models
 */
class ReglasContable extends Model
{
	protected $table = 'reglas_contables';

	protected $casts = [
		'categoria_producto_id' => 'int',
		'producto_id' => 'int',
		'cuenta_debe_id' => 'int',
		'cuenta_haber_id' => 'int',
		'prioridad' => 'int',
		'activa' => 'bool'
	];

	protected $fillable = [
		'tipo_movimiento',
		'categoria_producto_id',
		'producto_id',
		'cuenta_debe_id',
		'cuenta_haber_id',
		'prioridad',
		'activa',
		'descripcion'
	];

	public function categoria()
	{
		return $this->belongsTo(Categoria::class, 'categoria_producto_id');
	}

	public function cuenta()
	{
		return $this->belongsTo(Cuenta::class, 'cuenta_haber_id');
	}

	public function producto()
	{
		return $this->belongsTo(Producto::class);
	}
}
