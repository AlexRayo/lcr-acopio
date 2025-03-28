<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Liquidacion extends Model
{
  protected $table = 'liquidaciones';

  protected $fillable = [
    'fecha_liquidacion',
    'proveedor_id',
    'user_id',
    'tipo_cambio',
    'total_qq_liquidados',
    'total_qq_abonados',
    'precio_liquidacion',
    'monto_neto',
    'observaciones',
    'estado',
    'razon_anula',
    'fecha_anula',
    'usuario_anula',
  ];

  // NEW relation with User
  public function usuario()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function prestamos()
  {
    return $this->hasMany(Prestamo::class, 'prestamo_id');
  }
  public function proveedor()
  {
    return $this->belongsTo(Proveedor::class, 'proveedor_id');
  }


  // Relación con los detalles de la liquidación
  public function detalles()
  {
    return $this->hasMany(DetalleLiquidacion::class)->with('entrega');
  }
  // Nueva relación con abonos
  public function abonos()
  {
    return $this->hasMany(Abono::class, 'liquidacion_id');
  }

  protected static function booted()
  {
    // static::created no puede actualizar el campo de cada entrada 'liquidada' a true porque esta se crea después de este proceso
    //es por ello que debe actualizarse desde el modelo DetalleLiquidacion

    //actualizacion de caja solamente, por el momento
    static::created(function ($liquidacion) {
      DB::transaction(function () use ($liquidacion) {

        // Procesar salida de efectivo de caja
        if ($liquidacion->monto_neto > 0) {
          Caja::create([
            'monto' => $liquidacion->monto_neto,
            'tipo' => 'salida',
            'concepto' => Config('caja.concepto.LIQUIDACION'),
            'referencia' => $liquidacion->id,
            'user_id' => $liquidacion->user_id,
          ]);
        }
      });
    });

    //actualizacion de caja solamente, por el momento
    static::updated(function ($liquidacion) {
      $caja = Caja::where('referencia', $liquidacion->id)->first();
      if ($caja) {
        if ($liquidacion->estado == 'ANULADO') {
          $caja->estado = 'ANULADO';
          $caja->save();
        } else {
          $caja->estado = 'ACTIVO';
          $caja->save();
        }
      }
    });

    static::deleting(function ($liquidacion) {
      DB::transaction(function () use ($liquidacion) {

        //El saldo del préstamo debe ser actualizado aquí,
        //no se actualiza desde el modelo Abono porque ya se habrá eliminado la liquidacion antes y no se conocerá el abono_capital
        foreach ($liquidacion->abonos as $abono) {
          if ($abono->liquidacion_id === $liquidacion->id) { // Asegurarse de que el abono pertenece a esta liquidación
            $prestamo = $abono->prestamo;

            if ($prestamo) {
              $prestamo->saldo += $abono->abono_capital; // Revertir saldo
              $prestamo->save();
            }
          }
        }


        //el campo `liquidada` de cada entrada debe ser actualiza desde este proceso y no desde DetalleLiquidacion
        //de lo contrario no cambia porque no se encuentra la entrada, debido a que el detalle ya ha sido eliminado antes

        foreach ($liquidacion->detalles as $detalle) {
          $entrega = Entrega::find($detalle->entrega_id);
          if ($entrega) {
            $entrega->liquidada = false;
            $entrega->save();
          }
        }

        //revertir entrada de caja
        $caja = Caja::where('referencia', $liquidacion->id)->first();
        if ($caja) {
          $caja->delete();
        }

      });
    });
  }

}
