<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\PaymentMethodResource\Pages;
use App\Filament\Tenant\Resources\PaymentMethodResource\RelationManagers;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enums\PaymentType;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('payment_type')
                    ->required()
                    ->options(PaymentType::options())
                    ->label('Payment Type')
                    ->live(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Cash PHP, G-Cash, PayMaya, Visa, Points, etc.')
                    ->label('Payment Method Name'),

                Forms\Components\Section::make('Currency Details')
                    ->visible(fn (Forms\Get $get) => $get('payment_type') === 'cash')
                    ->schema([
                        Forms\Components\TextInput::make('currency_code')
                            ->label('Currency Code')
                            ->placeholder('PHP, USD, EUR')
                            ->maxLength(10)
                            ->requiredIf('payment_type', 'cash'),

                        Forms\Components\TextInput::make('currency_name')
                            ->label('Currency Name')
                            ->placeholder('Peso, US Dollar, Euro')
                            ->maxLength(100)
                            ->requiredIf('payment_type', 'cash'),

                        Forms\Components\TextInput::make('symbol')
                            ->label('Currency Symbol')
                            ->placeholder('₱, $, €')
                            ->maxLength(10)
                            ->requiredIf('payment_type', 'cash'),

                        Forms\Components\TextInput::make('exchange_rate')
                            ->label('Exchange Rate')
                            ->numeric()
                            ->default(1.0000)
                            ->step(0.0001)
                            ->minValue(0.0001)
                            ->required()
                            ->helperText('Exchange rate to base currency (1 unit of this currency = X units of base currency)'),

                        Forms\Components\Toggle::make('is_default_cash')
                            ->label('Set as Default Cash')
                            ->visible(fn (Forms\Get $get) => $get('payment_type') === 'cash')
                            ->helperText('Only one cash payment method can be default. Enabling this will automatically disable others.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
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
                Tables\Columns\TextColumn::make('payment_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => match($state) {
                        \App\Enums\PaymentType::CASH => 'success',
                        \App\Enums\PaymentType::CARD => 'info',
                        \App\Enums\PaymentType::E_WALLET => 'warning',
                        \App\Enums\PaymentType::CREDIT => 'danger',
                        \App\Enums\PaymentType::GIFT_CHECK => 'gray',
                        \App\Enums\PaymentType::POINTS => 'primary',
                    })
                    ->sortable()
                    ->label('Type'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Name'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->label('Sort'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_type')
                    ->options(PaymentType::options())
                    ->label('Payment Type'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'view' => Pages\ViewPaymentMethod::route('/{record}'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
