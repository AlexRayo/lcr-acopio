<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Log;

class Liquidacion extends Model
{
  protected $table = 'liquidaciones';

  protected $fillable = [
    'fecha_liquidacion',
    'user_id',
    'tipo_cambio',
    'total_qq_liquidados',
    'precio_liquidacion',
    'estado',
    'monto_neto',
    'observaciones',
  ];

  // NEW relation with User
  public function usuario()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function prestamo()
  {
    return $this->belongsTo(Prestamo::class);
  }

  // Relación con los detalles de la liquidación
  public function detalles()
  {
    return $this->hasMany(DetalleLiquidacion::class);
  }
  // Nueva relación con abonos
  public function abonos()
  {
    return $this->hasMany(Abono::class);
  }
  protected static function booted()
  {
    // static::created no puede actualizar el campo de cada entrada 'liquidada' a true porque esta se crea después de este proceso
    //es por ello que debe actualizarse desde el modelo DetalleLiquidacion

    static::deleting(function ($liquidacion) {
      DB::transaction(function () use ($liquidacion) {
        foreach ($liquidacion->abonos as $abono) {
          if ($abono->liquidacion_id === $liquidacion->id) { // Asegurarse de que el abono pertenece a esta liquidación
            $prestamo = $abono->prestamo;

            if ($prestamo) {
              Log::info("Saldo antes: " . $prestamo->saldo);
              log::info("Abono al capital" . $abono->abono_capital);
              Log::info("Saldo después: " . ($prestamo->saldo + $abono->abono_capital));
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
      });
    });
  }

}
