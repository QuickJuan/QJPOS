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

    protected static ?string $navigationGroup = 'System';

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

                TextInput::make('employee_code')
                    ->label('Employee Code')
                    ->maxLength(255)
                    ->unique('users', 'employee_code', ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->visibleOn('create'),

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
                    ->hint('Whether time-based OTP is enabled for this user')
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('otp_secret')
                    ->label('OTP Secret')
                    ->hint('The secret key for the authenticator app')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
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

                TextColumn::make('employee_code')
                    ->label('Employee Code')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

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
                        // Generate the secret and QR code
                        $otpData = OtpSecretService::generateSecret($record->email, config('app.name', 'QuickJuan'));

                        // Save to user
                        $record->update([
                            'otp_secret' => $otpData['secret'],
                            'otp_enabled' => true,
                            'otp_enabled_at' => now(),
                        ]);

                        // Store QR code in session for display
                        session()->put([
                            'otp_qr_code' => $otpData['qr_code'],
                            'otp_secret' => $otpData['secret'],
                        ]);

                        Notification::make()
                            ->success()
                            ->title('OTP Secret Generated!')
                            ->body("The user should scan the QR code. Secret: {$otpData['secret']}")
                            ->persistent()
                            ->send();
                    })
                    ->after(function (User $record) {
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
