<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\BranchResource\Pages;
use App\Filament\Tenant\Resources\BranchResource\RelationManagers;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('branch_code')
                    ->label('Branch Code')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('name')
                    ->label('Branch Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('address')
                    ->label('Address')
                    ->required()
                    ->maxLength(500),

                Forms\Components\TextInput::make('phone')
                    ->label('Phone')
                    ->required()
                    ->maxLength(20),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('contact_person')
                    ->label('Contact Person')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('long_lat')
                    ->label('Longitude/Latitude')
                    ->nullable()
                    ->maxLength(255)
                    ->helperText('Format: "longitude,latitude"'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Is Active')
                    ->default(true)
                    ->inline(false),

                Forms\Components\TextInput::make('tin')
                    ->label('TIN (Tax Identification Number)')
                    ->nullable()
                    ->maxLength(50)
                    ->helperText('VAT number for the branch'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch_code')
                    ->label('Branch Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Branch Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact Person'),

                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('tin')
                    ->label('TIN'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
