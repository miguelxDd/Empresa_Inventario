<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ParametrosInventario
 * 
 * @property int $id
 * @property string $clave
 * @property string $valor
 * @property string|null $descripcion
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class ParametrosInventario extends Model
{
	protected $table = 'parametros_inventario';
	public $timestamps = false;

	protected $fillable = [
		'clave',
		'valor',
		'descripcion'
	];
}
