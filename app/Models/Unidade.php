<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Unidade
 * 
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $abreviatura
 * @property bool $activa
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Producto[] $productos
 *
 * @package App\Models
 */
class Unidade extends Model
{
	protected $table = 'unidades';

	protected $casts = [
		'activa' => 'bool'
	];

	protected $fillable = [
		'codigo',
		'nombre',
		'abreviatura',
		'activa'
	];

	public function productos()
	{
		return $this->hasMany(Producto::class, 'unidad_id');
	}
}
