<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Asiento
 * 
 * @property int $id
 * @property Carbon $fecha
 * @property string $numero
 * @property string $descripcion
 * @property string|null $origen_tabla
 * @property int|null $origen_id
 * @property string $estado
 * @property float $total_debe
 * @property float $total_haber
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|AsientosDetalle[] $asientos_detalles
 * @property Collection|Movimiento[] $movimientos
 *
 * @package App\Models
 */
class Asiento extends Model
{
	protected $table = 'asientos';

	protected $casts = [
		'fecha' => 'datetime',
		'origen_id' => 'int',
		'total_debe' => 'float',
		'total_haber' => 'float',
		'created_by' => 'int'
	];

	protected $fillable = [
		'fecha',
		'numero',
		'descripcion',
		'origen_tabla',
		'origen_id',
		'estado',
		'total_debe',
		'total_haber',
		'created_by'
	];

	public function asientos_detalles()
	{
		return $this->hasMany(AsientosDetalle::class);
	}

	public function detalles()
	{
		return $this->hasMany(AsientosDetalle::class, 'asiento_id');
	}

	public function movimientos()
	{
		return $this->hasMany(Movimiento::class);
	}

	/**
	 * Relación con el usuario que creó el asiento
	 */
	public function createdBy()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	/**
	 * Obtener el movimiento de inventario relacionado (si existe)
	 */
	public function movimiento()
	{
		if ($this->origen_tabla === 'movimientos' && $this->origen_id) {
			return \App\Models\Movimiento::find($this->origen_id);
		}
		return null;
	}

	/**
	 * Scope para filtrar por rango de fechas
	 */
	public function scopeFechaEntre($query, $fechaInicio, $fechaFin)
	{
		return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
	}

	/**
	 * Scope para asientos confirmados
	 */
	public function scopeConfirmados($query)
	{
		return $query->where('estado', 'confirmado');
	}

	/**
	 * Verificar si el asiento está balanceado
	 */
	public function estaBalanceado(): bool
	{
		return $this->total_debe == $this->total_haber;
	}
}
