<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cuenta
 * 
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $tipo
 * @property int $nivel
 * @property int|null $padre_id
 * @property bool $activa
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Cuenta|null $cuenta
 * @property Collection|AsientosDetalle[] $asientos_detalles
 * @property Collection|Cuenta[] $cuentas
 * @property Collection|Producto[] $productos
 * @property Collection|ReglasContable[] $reglas_contables
 *
 * @package App\Models
 */
class Cuenta extends Model
{
	protected $table = 'cuentas';

	protected $casts = [
		'nivel' => 'int',
		'padre_id' => 'int',
		'activa' => 'bool'
	];

	protected $fillable = [
		'codigo',
		'nombre',
		'tipo',
		'nivel',
		'padre_id',
		'activa'
	];

	public function cuenta()
	{
		return $this->belongsTo(Cuenta::class, 'padre_id');
	}

	public function padre()
	{
		return $this->belongsTo(Cuenta::class, 'padre_id');
	}

	public function hijos()
	{
		return $this->hasMany(Cuenta::class, 'padre_id');
	}

	public function asientos_detalles()
	{
		return $this->hasMany(AsientosDetalle::class);
	}

	public function cuentas()
	{
		return $this->hasMany(Cuenta::class, 'padre_id');
	}

	public function productos()
	{
		return $this->hasMany(Producto::class, 'cuenta_inventario_id');
	}

	public function reglas_contables()
	{
		return $this->hasMany(ReglasContable::class, 'cuenta_haber_id');
	}

	/**
	 * Scope para cuentas activas
	 */
	public function scopeActivas($query)
	{
		return $query->where('activa', true);
	}

	/**
	 * Scope para búsqueda por código o nombre
	 */
	public function scopeBuscar($query, $termino)
	{
		return $query->where(function($q) use ($termino) {
			$q->where('codigo', 'LIKE', "%{$termino}%")
			  ->orWhere('nombre', 'LIKE', "%{$termino}%");
		});
	}

	/**
	 * Obtener el código y nombre completo
	 */
	public function getCodigoNombreAttribute()
	{
		return $this->codigo . ' - ' . $this->nombre;
	}
}
