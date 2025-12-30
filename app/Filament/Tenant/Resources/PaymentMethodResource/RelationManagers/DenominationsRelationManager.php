<?php

namespace App\Filament\Tenant\Resources\PaymentMethodResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DenominationsRelationManager extends RelationManager
{
    protected static string $relationship = 'denominations';

    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->label('Denomination Value')
                    ->helperText('The numeric value (e.g., 1, 5, 10, 20, 50, 100, 500, 1000)'),

                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(50)
                    ->label('Label')
                    ->helperText('Display label (e.g., "₱1", "₱5", "$1", "$5")'),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label('Sort Order')
                    ->helperText('Order in which denominations appear'),

                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->label('Active')
                    ->helperText('Only active denominations will appear in forms'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('value')
                    ->numeric()
                    ->sortable()
                    ->label('Value'),

                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->label('Label'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->label('Sort Order'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All denominations')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        // Only show denominations tab for cash payment methods
        return $ownerRecord->payment_type?->value === 'cash' || $ownerRecord->payment_type === 'cash';
    }
}
