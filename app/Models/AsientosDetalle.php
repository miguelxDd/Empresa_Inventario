<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AsientosDetalle
 * 
 * @property int $id
 * @property int $asiento_id
 * @property int $cuenta_id
 * @property float $debe
 * @property float $haber
 * @property int|null $centro_costo_id
 * @property string|null $concepto
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Asiento $asiento
 * @property Cuenta $cuenta
 *
 * @package App\Models
 */
class AsientosDetalle extends Model
{
	protected $table = 'asientos_detalle';

	protected $casts = [
		'asiento_id' => 'int',
		'cuenta_id' => 'int',
		'debe' => 'float',
		'haber' => 'float',
		'centro_costo_id' => 'int'
	];

	protected $fillable = [
		'asiento_id',
		'cuenta_id',
		'debe',
		'haber',
		'centro_costo_id',
		'concepto'
	];

	public function asiento()
	{
		return $this->belongsTo(Asiento::class);
	}

	public function cuenta()
	{
		return $this->belongsTo(Cuenta::class);
	}
}
