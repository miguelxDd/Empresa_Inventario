<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Existencia
 * 
 * @property int $producto_id
 * @property int $bodega_id
 * @property float $cantidad
 * @property float $costo_promedio
 * @property float|null $stock_minimo
 * @property float|null $stock_maximo
 * @property Carbon|null $updated_at
 * 
 * @property Bodega $bodega
 * @property Producto $producto
 *
 * @package App\Models
 */
class Existencia extends Model
{
	protected $table = 'existencias';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'producto_id' => 'int',
		'bodega_id' => 'int',
		'cantidad' => 'float',
		'costo_promedio' => 'float',
		'stock_minimo' => 'float',
		'stock_maximo' => 'float'
	];

	protected $fillable = [
		'cantidad',
		'costo_promedio',
		'stock_minimo',
		'stock_maximo'
	];

	public function bodega()
	{
		return $this->belongsTo(Bodega::class);
	}

	public function producto()
	{
		return $this->belongsTo(Producto::class);
	}
}
