<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CurrencyResource\Pages;
use App\Filament\Tenant\Resources\CurrencyResource\RelationManagers;
use App\Models\Currency;
use App\Enums\CurrencySymbol;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(3)
                    ->placeholder('PHP')
                    ->label('Currency Code'),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Peso')
                    ->label('Currency Name'),

                Forms\Components\Select::make('symbol')
                    ->required()
                    ->options(CurrencySymbol::options())
                    ->label('Currency Symbol')
                    ->searchable(),


                Forms\Components\Toggle::make('is_default')
                    ->label('Set as Default Currency')
                    ->helperText('Only one currency can be set as default')
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state) {
                            $set('exchange_rate', 1.0000);
                        }
                    })
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if ($value) {
                                    $hasDefault = Currency::where('is_default', true)
                                        ->when(request()->route('record'), function ($query, $recordId) {
                                            $query->where('id', '!=', $recordId);
                                        })
                                        ->exists();

                                    if ($hasDefault) {
                                        $fail('A default currency already exists. Please unset the current default first.');
                                    }
                                }
                            };
                        },
                    ]),

                Forms\Components\TextInput::make('exchange_rate')
                    ->required()
                    ->numeric()
                    ->default(1.0000)
                    ->step(0.0001)
                    ->label('Exchange Rate')
                    ->helperText('Exchange rate relative to default currency')
                    ->disabled(fn (Forms\Get $get) => $get('is_default'))
                    ->dehydrateStateUsing(fn ($state, Forms\Get $get) => $get('is_default') ? 1.0000 : $state),


                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Code'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Name'),

                Tables\Columns\TextColumn::make('symbol')
                    ->label('Symbol'),

                Tables\Columns\TextColumn::make('exchange_rate')
                    ->numeric(4)
                    ->sortable()
                    ->label('Exchange Rate'),

                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Currency'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DenominationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
