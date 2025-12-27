<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CurrencyDenominationResource\Pages;
use App\Models\CurrencyDenomination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CurrencyDenominationResource extends Resource
{
    protected static ?string $model = CurrencyDenomination::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Hide from sidebar - this will only be accessible as a sub-resource
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('currency_id')
                    ->relationship('currency', 'code')
                    ->required()
                    ->label('Currency')
                    ->searchable(),

                Forms\Components\TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->label('Denomination Value')
                    ->helperText('Enter the monetary value of this denomination'),

                Forms\Components\TextInput::make('label')
                    ->maxLength(255)
                    ->label('Label')
                    ->helperText('Optional display label (e.g., "₱100 Bill")')
                    ->placeholder('Auto-generated from currency symbol and value'),

                Forms\Components\TextInput::make('sort_order')
                    ->integer()
                    ->default(0)
                    ->label('Sort Order')
                    ->helperText('Lower numbers appear first'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('currency.code')
                    ->label('Currency')
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->numeric(2)
                    ->sortable()
                    ->label('Value'),

                Tables\Columns\TextColumn::make('label')
                    ->label('Label'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->label('Sort Order'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created'),
            ])
            ->filters([
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrencyDenominations::route('/'),
            'create' => Pages\CreateCurrencyDenomination::route('/create'),
            'edit' => Pages\EditCurrencyDenomination::route('/{record}/edit'),
        ];
    }
}
