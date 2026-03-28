<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\OtpSecretService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Notifications\Notification;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Roles and Permissions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique('users', 'email', ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->label('Password')
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8)
                    ->maxLength(255)
                    ->helperText(fn (string $context): ?string => $context === 'edit' ? 'Leave blank to keep current password.' : null)
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state)),

                Select::make('branches')
                    ->label('Branch')
                    ->relationship('branches', 'name')
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->preload()
                    ->placeholder('Select a branch'),

                Select::make('roles')
                    ->label('Roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->placeholder('Select roles')
                    ->helperText('Assign one or more roles to this user'),

                Toggle::make('otp_enabled')
                    ->label('OTP Enabled')
                    ->hint('Whether OTP is enabled for this user')
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('pincode')
                    ->label('PIN code')
                    ->hint(fn () => config('waiter.login_method') === 'pincode' ? 'PIN for waiter login' : 'PIN (used when pincode login is enabled)')
                    ->maxLength((int) config('waiter.pincode_length', 4))
                    ->minLength((int) config('waiter.pincode_length', 4))
                    ->numeric()
                    ->placeholder(fn () => 'Enter ' . config('waiter.pincode_length', 4) . '-digit PIN')
                    ->helperText('This PIN will be used for waiter login authentication')
                    ->visibleOn('edit')
                    ->formatStateUsing(fn () => null) // never display hashed pin
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                    ->dehydrated(fn ($state) => filled($state)),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('branches.name')
                    ->label('Branches')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                IconColumn::make('otp_enabled')
                    ->label('OTP')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->label('Filter by Role'),

                Tables\Filters\SelectFilter::make('branches')
                    ->relationship('branches', 'name')
                    ->multiple()
                    ->label('Filter by Branch'),
            ])
            ->actions([
                Tables\Actions\Action::make('generateOtp')
                    ->label('Generate OTP')
                    ->icon('heroicon-m-key')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Generate OTP Secret')
                    ->modalDescription('This will generate a new time-based OTP secret for this user. The user will need to scan the QR code with their authenticator app.')
                    ->modalSubmitActionLabel('Generate')
                    ->action(function (User $record) {
                        // Generate a proper TOTP secret for authenticator apps
                        $otpData = OtpSecretService::generateSecret($record->email, config('app.name', 'QuickJuan'));

                        // Save to user and refresh
                        $record->otp_secret = $otpData['secret'];
                        $record->otp_enabled = true;
                        $record->otp_enabled_at = now();
                        $record->save();
                        $record->refresh();

                        Notification::make()
                            ->success()
                            ->title('OTP Secret Generated!')
                            ->body('Scan the QR code to add this account to your authenticator app.')
                            ->persistent()
                            ->send();

                        // Redirect to show OTP page
                        return redirect()->route('filament.tenant.resources.users.show-otp-qr-code', $record);
                    }),
                Tables\Actions\Action::make('testOtp')
                    ->label('Test OTP')
                    ->icon('heroicon-m-check-circle')
                    ->color('info')
                    ->form([
                        TextInput::make('otp_code')
                            ->label('OTP Code')
                            ->hint('Enter a 6-digit code from the user\'s authenticator app')
                            ->maxLength(6)
                            ->inputMode('numeric')
                            ->required()
                            ->placeholder('000000'),
                    ])
                    ->action(function (User $record, array $data) {
                        if (!$record->otp_enabled || !$record->otp_secret) {
                            Notification::make()
                                ->danger()
                                ->title('OTP Not Enabled')
                                ->body('This user does not have OTP enabled.')
                                ->send();
                            return;
                        }

                        $code = $data['otp_code'];
                        $isValid = OtpSecretService::verifyCode($record->otp_secret, $code);

                        if ($isValid) {
                            Notification::make()
                                ->success()
                                ->title('OTP Code Valid!')
                                ->body("The code '$code' is valid for user {$record->name}.")
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('OTP Code Invalid!')
                                ->body("The code '$code' is not valid. Please check the code and try again.")
                                ->send();
                        }
                    })
                    ->visible(fn (User $record) => $record->otp_enabled),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
            'show-otp-qr-code' => Pages\ShowOtpQrCode::route('/{record}/otp-qr-code'),
        ];
    }
}
