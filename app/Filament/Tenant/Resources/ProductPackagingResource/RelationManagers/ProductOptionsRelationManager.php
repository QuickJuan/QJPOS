<?php

namespace App\Filament\Tenant\Resources\ProductPackagingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductOptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'productOptions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Option Name')
                    ->helperText('This is the name of the product option. e.g. Drinks, Side dish')
                    ->required()
                    ->maxLength(50),

                Forms\Components\TextInput::make('qty')
                    ->label('Limit quantity')
                    ->numeric()
                    ->required(),

                Forms\Components\Toggle::make('is_default')
                    ->label('default')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Option Name')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Limit quantity')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
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
            ]);
    }
}
