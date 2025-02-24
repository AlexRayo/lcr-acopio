<?php
namespace App\Helpers;

use App\Models\Prestamo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PrestamoHelper
{
  //********************* UTILS ***************************/
  public static function calcularAbono($prestamoId, $monto, $fechaPago)
  {
    $prestamo = Prestamo::with('proveedor')->find($prestamoId);

    if (!$prestamo) {
      return null;
    }

    $fechaUltimoPago = $prestamo->fecha_ultimo_pago ?: $prestamo->fecha_desembolso;
    $diasDiff = Carbon::parse($fechaUltimoPago)->diffInDays(Carbon::parse($fechaPago));

    $intereses = (($prestamo->monto * $prestamo->interes / 100) / 360) * $diasDiff;
    $abonoCapital = floatval($monto) - $intereses;

    return (object) [
      'diasDiff' => round($diasDiff),
      'intereses' => round($intereses, 2),
      'abonoCapital' => round($abonoCapital, 2),
    ];
  }

  //********************* UPDATE PRESTAMO TABLE ***************************/
  public static function updateOnCreate(Model $model): void
  {
    $prestamo = Prestamo::find($model->prestamo_id);

    if ($prestamo) {
      $prestamo->saldo -= $model->abono_capital;
      $prestamo->fecha_ultimo_pago = $model->fecha_liquidacion ?? $model->fecha_pago;
      $prestamo->save();
    } else {
      Log::warning("No se encontró registro del préstamo para el ID: {$model->id}");
    }
  }

  public static function updateOnDelete(Model $model): void
  {
    $prestamo = Prestamo::find($model->prestamo_id);

    if ($prestamo) {
      $prestamo->saldo += $model->abono_capital;
      $prestamo->fecha_ultimo_pago = $prestamo->fecha_desembolso ?? now();
      $prestamo->save();
    } else {
      Log::warning("No se encontró registro del préstamo para el ID: {$model->id}");
    }
  }

  public static function updateOnUpdate(Model $model): void
  {
    $oldCantidad = $model->getOriginal('monto');
    $deltaCantidad = $oldCantidad - $model->abono_capital;
    $prestamo = Prestamo::find($model->prestamo_id);

    if ($prestamo) {
      $prestamo->fecha_ultimo_pago = $model->fecha_liquidacion ?? $model->fecha_pago;
      if ($deltaCantidad > 0) {
        $prestamo->saldo += $model->abono_capital;
      } elseif ($deltaCantidad < 0) {
        $prestamo->saldo -= $model->abono_capital;
      }
      $prestamo->save();
    } else {
      Log::warning("No se encontró registro del préstamo para el ID: {$model->id}");
    }
  }
}
