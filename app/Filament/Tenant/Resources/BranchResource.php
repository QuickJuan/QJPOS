<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\BranchResource\Pages;
use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('branch_code')
                    ->label('Branch Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Branch Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address')
                    ->label('Address')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('contact_person')
                    ->label('Contact Person'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('tin')
                    ->label('TIN'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index'  => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit'   => Pages\EditBranch::route('/{record}/edit'),
            'view'   => Pages\ViewBranch::route('/{record}/view'),
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
