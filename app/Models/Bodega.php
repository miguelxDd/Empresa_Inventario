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
 * Class Bodega
 * 
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string|null $ubicacion
 * @property bool $activa
 * @property int|null $responsable_id
 * @property int $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Collection|Existencia[] $existencias
 * @property Collection|Movimiento[] $movimientos
 *
 * @package App\Models
 */
class Bodega extends Model
{
	use SoftDeletes;
	protected $table = 'bodegas';

	protected $casts = [
		'activa' => 'bool',
		'responsable_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'codigo',
		'nombre',
		'ubicacion',
		'activa',
		'responsable_id',
		'created_by',
		'updated_by'
	];

	public function existencias()
	{
		return $this->hasMany(Existencia::class);
	}

	public function movimientos()
	{
		return $this->hasMany(Movimiento::class, 'bodega_origen_id');
	}

	public function responsable()
	{
		return $this->belongsTo(User::class, 'responsable_id');
	}
}
