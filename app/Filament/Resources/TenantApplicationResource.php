<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantApplicationResource\Pages;
use App\Models\Central\TenantApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use App\Filament\Actions\ResendTenantApplicationEmail;
use App\Filament\Actions\ApproveTenantApplication;
use App\Filament\Actions\RejectTenantApplication;

class TenantApplicationResource extends Resource
{
    protected static ?string $model = TenantApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Tenant Applications';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Business Information')
                    ->description('Details about the business applying for StorePos')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('business_name')
                                    ->label('Business Name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('business_permit_number')
                                    ->label('Business Permit Number')
                                    ->maxLength(255),
                            ]),
                        Textarea::make('business_address')
                            ->label('Business Address')
                            ->required()
                            ->maxLength(500),
                    ]),

                Section::make('Owner Information')
                    ->description('Contact details of the business owner')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('owner_name')
                                    ->label('Owner Full Name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('owner_email')
                                    ->label('Owner Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        TextInput::make('owner_phone')
                            ->label('Owner Phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                    ]),

                Section::make('Logo')
                    ->description('Business logo stored in Spatie Media Library')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Business Logo')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(500)
                            ->imageResizeTargetHeight(500)
                            ->maxSize(5120)
                            ->disk('public')
                            ->directory('tenant-logos')
                            ->visibility('public')
                            ->getUploadedFileNameForStorageUsing(function ($file) {
                                return 'logo-' . time() . '.' . $file->getClientOriginalExtension();
                            })
                            ->beforeStateDehydrated(function ($component, $state, $record) {
                                if (empty($state) || !$record) {
                                    return;
                                }

                                // Store uploaded file in media library
                                if (is_array($state) && !empty($state)) {
                                    foreach ($state as $file) {
                                        if (is_string($file)) {
                                            $record->clearMediaCollection('logo');
                                            $record->addMedia(storage_path('app/public/' . $file))
                                                ->toMediaCollection('logo');
                                        }
                                    }
                                }
                            })
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->hasMedia('logo')) {
                                    $component->state([$record->getFirstMedia('logo')->getUrl()]);
                                }
                            }),
                    ]),

                Section::make('Application Status')
                    ->description('Review and manage the application')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->options([
                                        'pending' => 'Pending',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                    ])
                                    ->native(false),
                            ]),
                        Textarea::make('notes')
                            ->label('Admin Notes')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('business_name')
                    ->label('Business')
                    ->sortable()
                    ->searchable()
                    ->limit(40),
                TextColumn::make('owner_name')
                    ->label('Owner')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('owner_email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->limit(30),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Applied On')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                ApproveTenantApplication::make(),
                RejectTenantApplication::make(),
                ResendTenantApplicationEmail::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenantApplications::route('/'),
            'create' => Pages\CreateTenantApplication::route('/create'),
            'edit' => Pages\EditTenantApplication::route('/{record}/edit'),
        ];
    }
}
