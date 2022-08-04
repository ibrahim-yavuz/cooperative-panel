<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayerResource\Pages;
use App\Filament\Resources\PayerResource\RelationManagers;
use App\Models\Payer;
use App\Models\PayRecord;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use phpDocumentor\Reflection\Types\Integer;
use function Sodium\add;

class PayerResource extends Resource
{
    protected static ?string $model = Payer::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('second_name')
                    ->required()
                    ->maxLength(255)->default('Yavuz'),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('shares')
                    ->required()->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->sortable(),
                Tables\Columns\TextColumn::make('second_name'),
                Tables\Columns\TextColumn::make('phone_number'),
                Tables\Columns\TextColumn::make('shares')->sortable(),
                Tables\Columns\BooleanColumn::make('paid_this_month')->default(function(Payer $record) {
                    $pay_record = PayRecord::query()->where('payer_id', $record->id)->latest('created_at')->first();

                    if (!isset($pay_record->paid_date) || (Carbon::createFromTimeString($pay_record->paid_date)->month != Carbon::now()->month || Carbon::createFromTimeString($pay_record->paid_date)->year != Carbon::now()->year)){
                        return false;
                    }
                    return true;

                }),
                Tables\Columns\TextColumn::make('pay_records_count')->label('Paid Shares')->counts('pay_records'),
            ])
            ->filters([
                Tables\Filters\Filter::make('first_name')
                    ->form([
                        Forms\Components\TextInput::make('first_name'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['first_name'],
                                fn (Builder $query, $name): Builder => $query->where('first_name', 'LIKE', '%'.$name.'%'),
                            );
                    }),
                Tables\Filters\SelectFilter::make('is_paid')->form([Forms\Components\Checkbox::make('is_paid')])
                    ->options(['true' => 'True', 'false' => 'False'])
                    ->query(function (Builder $query, array $data) {
                            $payersWhoPaid = Payer::with('pay_records')->get()->filter(function($payer){
                                $paid_date = Carbon::createFromTimeString($payer->pay_records->last()->paid_date);
                                if($paid_date->year == Carbon::now()->year && $paid_date->month == Carbon::now()->month){
                                    if($paid_date->day >= 1 && $paid_date->day <= Carbon::now()->daysInMonth){
                                        return $payer;
                                    }
                                }
                            });

                            $payersIds = [];

                            foreach ($payersWhoPaid as $payer_){
                                $payersIds[] = $payer_->id;
                            }

                        $payersWhoDidntPay = Payer::with('pay_records')->get()->filter(function($payer){
                            $paid_date = Carbon::createFromTimeString($payer->pay_records->last()->paid_date);
                            if($paid_date->month != Carbon::now()->month){
                                return $payer;
                            }
                        });

                        $notPayersIds = [];

                        foreach ($payersWhoDidntPay as $payer_){
                            $notPayersIds[] = $payer_->id;
                        }

                        if($data['is_paid']){
                            return $query->whereIn('id', $payersIds);
                        }
                        return $query->whereIn('id', $notPayersIds);
                        },
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pay_records')->label('Pay Records')
                    ->tooltip(fn (Payer $record): string => 'Pay Records of '.$record->first_name.' '.$record->second_name)
                    ->url(fn (Payer $record): string => 'pay-records?tableFilters[payer_id][payer_id]='.$record->id),
                Tables\Actions\Action::make('pay')->requiresConfirmation()->label('Pay Monthly Share')
                    ->action(function(Payer $record) {
                        $pay_record = PayRecord::query()->where('payer_id', $record->id)->latest('created_at')->first();

                        if (!isset($pay_record->created_at) || ($pay_record->created_at->month != Carbon::now()->month || $pay_record->created_at->year != Carbon::now()->year)){
                            $pay_record = new PayRecord();
                            $pay_record->payer_id = $record->id;
                            $pay_record->save();

                            Notification::make()
                                ->title('Successfully paid monthly share')
                                ->success()
                                ->send();
                        }else{
                            Notification::make()
                                ->title('Already paid monthly share')
                                ->warning()
                                ->send();
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])->defaultSort('first_name');
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
            'index' => Pages\ListPayers::route('/'),
            'create' => Pages\CreatePayer::route('/create'),
            'edit' => Pages\EditPayer::route('/{record}/edit'),
        ];
    }
}
