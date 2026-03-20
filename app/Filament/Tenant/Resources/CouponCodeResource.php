<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CouponCodeResource\Pages;
use App\Models\CouponCode;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponCodeResource extends Resource
{
    protected static ?string $model = CouponCode::class;

    protected static ?string $navigationIcon  = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Store';
    protected static ?int    $navigationSort  = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Black Friday Sale'),

                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(20)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('e.g., BLACKFRIDAY20'),
                                // ->suffixAction(
                                //     Forms\Components\Actions\Action::make('generate')
                                //         ->icon('heroicon-m-sparkles')
                                //         ->action(function (Forms\Set $set) {
                                //             $couponService = app(CouponService::class);
                                //             $set('code', $couponService->generateUniqueCouponCode());
                                //         })
                                // ),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->placeholder('Optional description for internal use'),
                    ]),

                Section::make('Discount Configuration')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->required()
                                    ->options([
                                        'percentage'   => 'Percentage Discount',
                                        'fixed_amount' => 'Fixed Amount Discount',
                                    ])
                                    ->live()
                                    ->afterStateUpdated(fn(Forms\Set $set) => $set('value', null)),

                                Forms\Components\TextInput::make('value')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix(function (Forms\Get $get) {
                                        return $get('type') === 'percentage' ? '%' : '$';
                                    })
                                    ->placeholder(function (Forms\Get $get) {
                                        return $get('type') === 'percentage' ? 'e.g., 20' : 'e.g., 10.00';
                                    })
                                    ->step(0.01)
                                    ->formatStateUsing(function ($state, Forms\Get $get) {
                                        if ($get('type') === 'fixed_amount' && $state) {
                                            return number_format($state / 100, 2);
                                        }
                                        return $state;
                                    })
                                    ->dehydrateStateUsing(function ($state, Forms\Get $get) {
                                        if ($get('type') === 'fixed_amount') {
                                            return (int) round($state * 100);
                                        }
                                        return (int) $state;
                                    }),

                                Forms\Components\TextInput::make('minimum_amount')
                                    ->label('Minimum Order Amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix('$')
                                    ->placeholder('e.g., 50.00')
                                    ->step(0.01)
                                    ->formatStateUsing(fn($state) => $state ? number_format($state / 100, 2) : null)
                                    ->dehydrateStateUsing(fn($state) => $state ? (int) round($state * 100) : null),
                            ]),
                    ]),

                Section::make('Usage Limits')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('usage_limit')
                                    ->label('Total Usage Limit')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Leave empty for unlimited'),

                                Forms\Components\TextInput::make('usage_limit_per_user')
                                    ->label('Usage Limit Per User')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Leave empty for unlimited'),
                            ]),
                    ]),

                Section::make('Validity Period')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('valid_from')
                                    ->label('Valid From')
                                    ->placeholder('Leave empty to start immediately'),

                                Forms\Components\DateTimePicker::make('valid_until')
                                    ->label('Valid Until')
                                    ->placeholder('Leave empty for no expiry'),
                            ]),
                    ]),

                Section::make('Product & Category Restrictions')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('applicable_products')
                                    ->label('Applicable Products')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->options(function () {
                                        return \App\Models\Product::where('name', 'published')
                                        // return \App\Models\Product::where('status', 'published')
                                            ->orderBy('name')
                                            ->pluck('name', 'id');
                                    })
                                    ->placeholder('Leave empty for all products')
                                    ->helperText('Select specific products this coupon applies to. Leave empty to apply to all products.')
                                    ->optionsLimit(50),

                                Forms\Components\Select::make('applicable_categories')
                                    ->label('Applicable Categories')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    // ->options(function () {
                                    //     return \App\Models\ProductCategory::where('is_active', true)
                                    //         ->orderBy('title')
                                    //         ->pluck('title', 'id');
                                    // })
                                    ->placeholder('Leave empty for all categories')
                                    ->helperText('Select specific categories this coupon applies to. Leave empty to apply to all categories.')
                                    ->optionsLimit(50),
                            ]),
                    ]),

                Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive coupons cannot be used'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->sortable()
                    ->searchable()
                    ->words(4),

                TextColumn::make('type')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('value')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('minimum_amount')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('usage_limit')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('usage_limit_per_user')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('used_count')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('valid_from')
                    ->sortable()
                    ->searchable()
                    ->dateTime('F d Y, h:i:s A'),

                TextColumn::make('valid_until')
                    ->sortable()
                    ->searchable()
                    ->dateTime('F d Y, h:i:s A'),

                IconColumn::make('is_active')
                    ->sortable()
                    ->searchable()
                    ->boolean(),
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
            'index'  => Pages\ListCouponCodes::route('/'),
            'create' => Pages\CreateCouponCode::route('/create'),
            'edit'   => Pages\EditCouponCode::route('/{record}/edit'),
            'view'   => Pages\ViewCouponCode::route('/{record}/view'),
        ];
    }
}
