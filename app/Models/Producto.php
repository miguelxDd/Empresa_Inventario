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
 * Class Producto
 * 
 * @property int $id
 * @property string $sku
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $unidad_id
 * @property int $categoria_id
 * @property float|null $precio_compra_promedio
 * @property float|null $precio_venta
 * @property bool $activo
 * @property bool $permite_negativo
 * @property int|null $cuenta_inventario_id
 * @property int|null $cuenta_costo_id
 * @property int|null $cuenta_contraparte_id
 * @property int $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Categoria $categoria
 * @property Cuenta|null $cuenta
 * @property Unidade $unidade
 * @property Collection|Existencia[] $existencias
 * @property Collection|MovimientoDetalle[] $movimiento_detalles
 * @property Collection|ReglasContable[] $reglas_contables
 *
 * @package App\Models
 */
class Producto extends Model
{
	use SoftDeletes;
	protected $table = 'productos';

	protected $casts = [
		'unidad_id' => 'int',
		'categoria_id' => 'int',
		'precio_compra_promedio' => 'float',
		'precio_venta' => 'float',
		'activo' => 'bool',
		'permite_negativo' => 'bool',
		'cuenta_inventario_id' => 'int',
		'cuenta_costo_id' => 'int',
		'cuenta_contraparte_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'sku',
		'nombre',
		'descripcion',
		'unidad_id',
		'categoria_id',
		'precio_compra_promedio',
		'precio_venta',
		'activo',
		'permite_negativo',
		'cuenta_inventario_id',
		'cuenta_costo_id',
		'cuenta_contraparte_id',
		'created_by',
		'updated_by'
	];

	public function categoria()
	{
		return $this->belongsTo(Categoria::class);
	}

	public function cuenta()
	{
		return $this->belongsTo(Cuenta::class, 'cuenta_inventario_id');
	}

	public function unidade()
	{
		return $this->belongsTo(Unidade::class, 'unidad_id');
	}

	public function existencias()
	{
		return $this->hasMany(Existencia::class);
	}

	public function movimiento_detalles()
	{
		return $this->hasMany(MovimientoDetalle::class);
	}

	public function reglas_contables()
	{
		return $this->hasMany(ReglasContable::class);
	}
}
