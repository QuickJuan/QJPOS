<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CustomerResource\Pages;
use App\Filament\Tenant\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Enums\CustomerType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Customer Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Information')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Customer Name'),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Birth Date')
                            ->maxDate(now()),

                        Forms\Components\TextInput::make('contact_no')
                            ->tel()
                            ->maxLength(255)
                            ->label('Contact Number'),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->label('Email Address'),

                        Forms\Components\Select::make('type')
                            ->options(CustomerType::options())
                            ->default(CustomerType::REGULAR->value)
                            ->required()
                            ->label('Customer Type'),

                        Forms\Components\DateTimePicker::make('last_visit')
                            ->label('Last Visit')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Preferences')
                    ->schema([
                        Forms\Components\Toggle::make('email_subscribe')
                            ->label('Subscribe to Email')
                            ->default(false),

                        Forms\Components\Toggle::make('sms_subscribe')
                            ->label('Subscribe to SMS')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('E-Wallet & Loyalty Points')
                    ->schema([
                        Forms\Components\TextInput::make('eWallet.balance')
                            ->label('E-Wallet Balance')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->default(0)
                            ->prefix('₱')
                            ->helperText('Available e-wallet balance'),

                        Forms\Components\TextInput::make('eWallet.earned_points')
                            ->label('Earned Points')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->default(0)
                            ->suffix('pts'),

                        Forms\Components\TextInput::make('eWallet.redeemed_points')
                            ->label('Redeemed Points')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->default(0)
                            ->suffix('pts'),

                        Forms\Components\TextInput::make('eWallet.points_balance')
                            ->label('Points Balance')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->default(0)
                            ->suffix('pts')
                            ->helperText('Points available for redemption'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->hidden(fn ($record) => $record && !$record->eWallet),

                Forms\Components\Section::make('Profile Picture')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('profile')
                            ->collection('profile')
                            ->image()
                            ->maxSize(2048)
                            ->imageEditor()
                            ->label('Profile Picture'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('profile')
                    ->collection('profile')
                    ->circular()
                    ->label('Photo'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable()
                    ->label('Name'),

                Tables\Columns\TextColumn::make('contact_no')
                    ->searchable()
                    ->label('Contact'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => CustomerType::VIP->value,
                        'primary' => CustomerType::REGULAR->value,
                    ])
                    ->formatStateUsing(fn ($state): string => $state instanceof CustomerType ? $state->label() : CustomerType::from($state)->label())
                    ->sortable()
                    ->label('Type'),

                Tables\Columns\TextColumn::make('eWallet.points_balance')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->label('Points Balance')
                    ->suffix(' pts')
                    ->color('primary')
                    ->default('0.00'),

                Tables\Columns\TextColumn::make('eWallet.balance')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->label('E-Wallet Balance')
                    ->prefix('₱')
                    ->color('success')
                    ->default('0.00'),

                Tables\Columns\TextColumn::make('last_visit')
                    ->dateTime()
                    ->sortable()
                    ->label('Last Visit')
                    ->placeholder('Never'),

                Tables\Columns\IconColumn::make('email_subscribe')
                    ->boolean()
                    ->label('Email'),

                Tables\Columns\IconColumn::make('sms_subscribe')
                    ->boolean()
                    ->label('SMS'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(CustomerType::options())
                    ->label('Customer Type'),

                Tables\Filters\TernaryFilter::make('email_subscribe')
                    ->label('Email Subscribed'),

                Tables\Filters\TernaryFilter::make('sms_subscribe')
                    ->label('SMS Subscribed'),
            ])
            ->actions([
                Tables\Actions\Action::make('create_ewallet')
                    ->label('Create E-Wallet')
                    ->icon('heroicon-o-wallet')
                    ->color('success')
                    ->visible(fn ($record) => !$record->eWallet)
                    ->requiresConfirmation()
                    ->modalHeading('Create E-Wallet')
                    ->modalDescription('This will create an e-wallet account for this customer with zero balance.')
                    ->action(function ($record) {
                        $record->eWallet()->create([
                            'balance' => 0,
                            'total_loaded' => 0,
                            'total_spent' => 0,
                            'earned_points' => 0,
                            'redeemed_points' => 0,
                            'points_balance' => 0,
                            'is_active' => true,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('E-Wallet Created')
                            ->success()
                            ->body('E-wallet account has been created for ' . $record->customer_name)
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('create_ewallets')
                        ->label('Create E-Wallets')
                        ->icon('heroicon-o-wallet')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Create E-Wallets for Selected Customers')
                        ->modalDescription('This will create e-wallet accounts for all selected customers that don\'t have one.')
                        ->action(function ($records) {
                            $created = 0;
                            foreach ($records as $record) {
                                if (!$record->eWallet) {
                                    $record->eWallet()->create([
                                        'balance' => 0,
                                        'total_loaded' => 0,
                                        'total_spent' => 0,
                                        'earned_points' => 0,
                                        'redeemed_points' => 0,
                                        'points_balance' => 0,
                                        'is_active' => true,
                                    ]);
                                    $created++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('E-Wallets Created')
                                ->success()
                                ->body("Created e-wallet accounts for {$created} customer(s)")
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
