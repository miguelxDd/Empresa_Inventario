<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Categoria
 * 
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string|null $descripcion
 * @property bool $activa
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Producto[] $productos
 * @property Collection|ReglasContable[] $reglas_contables
 *
 * @package App\Models
 */
class Categoria extends Model
{
	protected $table = 'categorias';

	protected $casts = [
		'activa' => 'bool'
	];

	protected $fillable = [
		'codigo',
		'nombre',
		'descripcion',
		'activa'
	];

	public function productos()
	{
		return $this->hasMany(Producto::class);
	}

	public function reglas_contables()
	{
		return $this->hasMany(ReglasContable::class, 'categoria_producto_id');
	}
}
