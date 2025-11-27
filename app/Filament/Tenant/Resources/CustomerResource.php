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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
