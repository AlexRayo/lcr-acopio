<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioResource\Pages;
use App\Models\Inventario;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventarioResource extends Resource
{
    protected static ?string $model = Inventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tipo_cafe')
                    ->label('Tipo de Café')
                    ->sortable(),
                TextColumn::make('humedad')
                    ->label('Humedad')
                    ->sortable(),
                TextColumn::make('cantidad_sacos')
                    ->label('Cantidad de Sacos')
                    ->sortable(),
                TextColumn::make('peso_neto')
                    ->label('Peso Neto (Quintales)')
                    ->formatStateUsing(fn(string $state): string => number_format($state, 2))
                    ->sortable(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarios::route('/'),
            'create' => Pages\CreateInventario::route('/create'),
            'edit' => Pages\EditInventario::route('/{record}/edit'),
        ];
    }
}
