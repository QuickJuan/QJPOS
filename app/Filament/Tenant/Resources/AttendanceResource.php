<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),

                Hidden::make('user_id')
                    ->default(Auth::id()),

                DatePicker::make('attendance_date')
                    ->label('Attendance Date')
                    ->required()
                    ->default(now()),

                DateTimePicker::make('schedule_timein')
                    ->label('Scheduled Time In')
                    ->required(),

                DateTimePicker::make('schedule_timeout')
                    ->label('Scheduled Time Out')
                    ->required(),

                DateTimePicker::make('actual_timein')
                    ->label('Actual Time In')
                    ->required(),

                DateTimePicker::make('actual_timeout')
                    ->label('Actual Time Out')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('branch.name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('attendance_date')
                    ->label('Attendance Date')
                    ->date(),

                TextColumn::make('schedule_timein')
                    ->label('Scheduled Time In')
                    ->dateTime('F d, Y h:i:s A'),

                TextColumn::make('schedule_timeout')
                    ->label('Scheduled Time Out')
                    ->dateTime('F d, Y h:i:s A'),

                TextColumn::make('actual_timein')
                    ->label('Actual Time In')
                    ->dateTime('F d, Y h:i:s A'),

                TextColumn::make('actual_timeout')
                    ->label('Actual Time Out')
                    ->dateTime('F d, Y h:i:s A'),
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
            'index'  => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit'   => Pages\EditAttendance::route('/{record}/edit'),
            'view'   => Pages\ViewAttendance::route('/{record}/view'),
        ];
    }
}
