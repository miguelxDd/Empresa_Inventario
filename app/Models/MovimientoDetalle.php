<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MovimientoDetalle
 * 
 * @property int $id
 * @property int $movimiento_id
 * @property int $producto_id
 * @property float $cantidad
 * @property float $costo_unitario
 * @property float $total
 * @property string|null $observaciones
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Movimiento $movimiento
 * @property Producto $producto
 *
 * @package App\Models
 */
class MovimientoDetalle extends Model
{
	protected $table = 'movimiento_detalles';

	protected $casts = [
		'movimiento_id' => 'int',
		'producto_id' => 'int',
		'cantidad' => 'float',
		'costo_unitario' => 'float',
		'total' => 'float'
	];

	protected $fillable = [
		'movimiento_id',
		'producto_id',
		'cantidad',
		'costo_unitario',
		'total',
		'observaciones'
	];

	public function movimiento()
	{
		return $this->belongsTo(Movimiento::class);
	}

	public function producto()
	{
		return $this->belongsTo(Producto::class);
	}
}
