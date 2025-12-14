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
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        // If not cash, set currency to default
                        if ($state !== 'cash') {
                            $defaultCurrency = \App\Models\Currency::where('is_default', true)->first();
                            if ($defaultCurrency) {
                                $set('currency_id', $defaultCurrency->id);
                            }
                        }
                    }),

                Forms\Components\Select::make('currency_id')
                    ->label('Currency')
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Forms\Get $get) => $get('payment_type') === 'cash')
                    ->required(fn (Forms\Get $get) => $get('payment_type') === 'cash')
                    ->helperText('Select currency for cash payments'),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('G-Cash, PayMaya, Visa, etc.')
                    ->label('Payment Method Name'),

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
                    ->label('Sort Order'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_type')
                    ->options(PaymentType::options())
                    ->label('Payment Type'),
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
            ])
            ->defaultSort('sort_order');
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
