<?php
namespace App\Filament\Tenant\Resources;

use Filament\Tables;
use App\Models\Branch;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Filament\Tenant\Resources\BranchResource\Pages;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('branch_code')
                    ->label('Branch Code')
                    ->required()
                    ->maxLength(255),

                TextInput::make('name')
                    ->label('Branch Name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('address')
                    ->label('Address')
                    ->required()
                    ->maxLength(500),

                TextInput::make('phone')
                    ->label('Phone')
                    ->required()
                    ->maxLength(20),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                TextInput::make('contact_person')
                    ->label('Contact Person')
                    ->required()
                    ->maxLength(255),

                TextInput::make('long_lat')
                    ->label('Longitude/Latitude')
                    ->nullable()
                    ->maxLength(255)
                    ->helperText('Format: "longitude,latitude"'),

                Toggle::make('is_active')
                    ->label('Is Active')
                    ->default(true)
                    ->inline(false),

                TextInput::make('tin')
                    ->label('TIN (Tax Identification Number)')
                    ->nullable()
                    ->maxLength(50)
                    ->helperText('VAT number for the branch'),

                Select::make('users')
                    ->label('User')
                    ->relationship('users', 'name')
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->preload()
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
            'index'  => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit'   => Pages\EditBranch::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Store';
    }

    public static function getNavigationSort(): ?int
    {
        return 1; // Second in group
    }
}
