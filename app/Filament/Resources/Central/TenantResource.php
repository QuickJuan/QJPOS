<?php

namespace App\Filament\Resources\Central;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Central\Tenant;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use App\Enums\BillingType;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Central\TenantResource\Pages;
use App\Filament\Resources\Central\TenantResource\RelationManagers;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email(),
                Forms\Components\TextInput::make('tenancy_db_name')
                    ->required(),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\TextInput::make('address'),
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->label('Domain/Subdomain')
                    ->helperText('This will be used to create the tenant domain')
                    ->dehydrated(),
                Forms\Components\Select::make('billing_type')
                    ->required()
                    ->options(BillingType::options())
                    ->default(BillingType::FREE->value),
                Forms\Components\Select::make('subscription')
                    ->required()
                    ->label('Subscription Plan')
                    ->options(PlanType::options())
                    ->default(PlanType::STARTER->value),
                Forms\Components\Select::make('subscription_status')
                    ->required()
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ])
                    ->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('subscription')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('billing_type')
                    ->label('Billing Type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('subscription_status')
                    ->label('Subscription Status')


            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subscription_status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),

                Tables\Filters\SelectFilter::make('subscription')
                    ->options([
                        'starter' => 'Starter',
                        'professional' => 'Professional',
                        'enterprise' => 'Enterprise',
                    ]),

                Tables\Filters\SelectFilter::make('billing_type')
                    ->options([
                        'free' => 'Free',
                        'transactional' => 'Transactional',
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                    ]),
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
            // RelationManagers\DomainsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

}
