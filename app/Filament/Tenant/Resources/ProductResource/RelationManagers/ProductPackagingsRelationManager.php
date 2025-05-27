<?php

namespace App\Filament\Tenant\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class ProductPackagingsRelationManager extends RelationManager
{
    protected static string $relationship = 'productPackagings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_measure')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('cost')
                    ->required()
                    ->numeric()
                    ->label('Unit Cost'),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->label('Unit Price'),
                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->numeric()
                    ->label('Quantity'),
                SpatieMediaLibraryFileUpload::make('featured_image')
                    ->collection('featured_image')
                    ->image()
                    ->label('Featured Image'),
                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('unit_measure')
            ->columns([
                Tables\Columns\TextColumn::make('unit_measure'),
                Tables\Columns\TextColumn::make('cost')
                    ->money('php', true)
                    ->label('Unit Cost'),
                Tables\Columns\TextColumn::make('price')
                    ->money('php', true)
                    ->label('Unit Price'),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Quantity'),
                
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
