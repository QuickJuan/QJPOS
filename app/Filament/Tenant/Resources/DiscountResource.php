<?php
namespace App\Filament\Tenant\Resources;

use App\Enums\Discount\DiscountType;
use App\Enums\Discount\TypeEnum;
use App\Filament\Tenant\Resources\DiscountResource\Pages;
use App\Models\Discount;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Store';
    protected static ?int    $navigationSort  = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('discount_name')
                    ->label('Discount Name')
                    ->required(),

                TextInput::make('amount')
                    ->numeric()
                    ->required(),

                Select::make('type')
                    ->options(TypeEnum::filamentOptions())
                    ->required()
                    ->default(TypeEnum::PERCENTAGE->value),

                Toggle::make('remove_tax')
                    ->default(false),

                Select::make('discount_type')
                    ->options(DiscountType::filamentOptions())
                    ->required()
                    ->default(DiscountType::REGULAR->value),

                Toggle::make('require_customer_info')
                    ->default(false),

                Toggle::make('required_pax')
                    ->label('Requires PAX Division')
                    ->helperText('Enable this if discount should be divided among multiple people')
                    ->default(false),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('discount_name')
                    ->label('Discount Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('remove_tax')
                    ->sortable(),

                TextColumn::make('discount_type')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('require_customer_info')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order', 'asc')
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
            'index'  => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit'   => Pages\EditDiscount::route('/{record}/edit'),
            'view'   => Pages\ViewDiscount::route('/{record}/view'),
        ];
    }
}
