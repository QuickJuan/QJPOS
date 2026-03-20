<?php

namespace App\Filament\Tenant\Widgets;

use App\Filament\Tenant\Resources\EventResource;
use App\Models\Event;
use App\Models\User;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class EventCalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Events & Reservations';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    public Model|string|null $model = Event::class;

    public function fetchEvents(array $info): array
    {
        return Event::query()
            ->whereBetween('start_at', [$info['start'], $info['end']])
            ->orWhereBetween('end_at', [$info['start'], $info['end']])
            ->get()
            ->map(fn (Event $event) => EventData::make()
                ->id($event->id)
                ->title($event->title . ' [' . ucfirst($event->category) . ']')
                ->start($event->start_at)
                ->end($event->end_at)
                ->backgroundColor($event->categoryColor())
                ->borderColor($event->categoryColor())
                ->textColor('#ffffff')
                ->extendedProps([
                    'status'      => $event->status,
                    'category'    => $event->category,
                    'description' => $event->description,
                    'assigned_to' => $event->user?->name,
                ])
            )
            ->toArray();
    }

    public function getFormSchema(): array
    {
        return EventResource::formSchema();
    }

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mountUsing(function (Forms\Form $form, array $arguments) {
                    $form->fill([
                        'start_at' => $arguments['start'] ?? null,
                        'end_at'   => $arguments['end'] ?? null,
                    ]);
                }),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->mountUsing(function (Event $record, Forms\Form $form, array $arguments) {
                    $form->fill([
                        'title'       => $record->title,
                        'description' => $record->description,
                        'category'    => $record->category,
                        'status'      => $record->status,
                        'start_at'    => $arguments['event']['start'] ?? $record->start_at,
                        'end_at'      => $arguments['event']['end'] ?? $record->end_at,
                        'user_id'     => $record->user_id,
                    ]);
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function viewAction(): \Filament\Actions\Action
    {
        return Actions\ViewAction::make();
    }
}
