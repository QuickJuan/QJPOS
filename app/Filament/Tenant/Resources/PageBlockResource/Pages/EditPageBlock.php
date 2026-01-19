<?php

namespace App\Filament\Tenant\Resources\PageBlockResource\Pages;

use App\Filament\Tenant\Resources\PageBlockResource;
use App\Filament\Forms\BlockFormBuilder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditPageBlock extends EditRecord
{
    protected static string $resource = PageBlockResource::class;

    public function form(Form $form): Form
    {
        $blockType = $this->record->blockType;
        $blockTypeSlug = $blockType?->slug;

        return $form
            ->schema([
                Section::make('Block Information')
                    ->description('Edit block details')
                    ->schema([
                        TextInput::make('blockType.name')
                            ->label('Block Type')
                            ->disabled(),

                        TextInput::make('order')
                            ->numeric()
                            ->helperText('Display order (lower numbers appear first)'),

                        TextInput::make('page.title')
                            ->label('Page')
                            ->disabled(),
                    ])->columns(2),

                ...$blockTypeSlug ? BlockFormBuilder::getFormSchema($blockTypeSlug) : [],
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
