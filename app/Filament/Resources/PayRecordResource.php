<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayRecordResource\Pages;
use App\Filament\Resources\PayRecordResource\RelationManagers;
use App\Models\Payer;
use App\Models\PayRecord;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayRecordResource extends Resource
{
    protected static ?string $model = PayRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('payer_id')
                    ->required(),
                Forms\Components\TimePicker::make('paid_date')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payer_first_name')->default(fn(PayRecord $record) => Payer::find($record->payer_id)->first_name),
                Tables\Columns\TextColumn::make('payer_second_name')->default(fn(PayRecord $record) => Payer::find($record->payer_id)->second_name),
                Tables\Columns\TextColumn::make('paid_date')->dateTime("d M y"),
            ])
            ->filters([
                Tables\Filters\MultiSelectFilter::make('payer')->relationship('payer', 'first_name'),
                Tables\Filters\Filter::make('paid_date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('paid_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('paid_date', '<=', $date),
                            );
                    }),
                ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPayRecords::route('/'),
            'create' => Pages\CreatePayRecord::route('/create'),
            'edit' => Pages\EditPayRecord::route('/{record}/edit'),
        ];
    }
}
