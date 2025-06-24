<?php
namespace App\Filament\Tenant\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Tenant\Resources\AttendanceResource\Pages;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                TextColumn::make('attendance_date')
                    ->label('Attendance Date')
                    ->date(),

                TextColumn::make('schedule_timein')
                    ->label('Scheduled Time In')
                    ->dateTime(),

                TextColumn::make('schedule_timeout')
                    ->label('Scheduled Time Out')
                    ->dateTime(),

                TextColumn::make('actual_timein')
                    ->label('Actual Time In')
                    ->dateTime(),

                TextColumn::make('actual_timeout')
                    ->label('Actual Time Out')
                    ->dateTime(),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit'   => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
