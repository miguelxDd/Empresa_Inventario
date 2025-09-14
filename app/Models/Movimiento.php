<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Movimiento
 * 
 * @property int $id
 * @property Carbon $fecha
 * @property string $tipo
 * @property int|null $bodega_origen_id
 * @property int|null $bodega_destino_id
 * @property string $estado
 * @property string|null $numero_documento
 * @property string|null $referencia
 * @property string|null $observaciones
 * @property float $valor_total
 * @property int|null $asiento_id
 * @property int $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Asiento|null $asiento
 * @property Bodega|null $bodega
 * @property Collection|MovimientoDetalle[] $movimiento_detalles
 *
 * @package App\Models
 */
class Movimiento extends Model
{
	use SoftDeletes;
	protected $table = 'movimientos';

	protected $casts = [
		'fecha' => 'datetime',
		'bodega_origen_id' => 'int',
		'bodega_destino_id' => 'int',
		'valor_total' => 'float',
		'asiento_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'fecha',
		'tipo',
		'bodega_origen_id',
		'bodega_destino_id',
		'estado',
		'numero_documento',
		'referencia',
		'observaciones',
		'valor_total',
		'asiento_id',
		'created_by',
		'updated_by'
	];

	public function asiento()
	{
		return $this->belongsTo(Asiento::class);
	}

	public function bodega()
	{
		return $this->belongsTo(Bodega::class, 'bodega_origen_id');
	}

	public function movimiento_detalles()
	{
		return $this->hasMany(MovimientoDetalle::class);
	}
}
