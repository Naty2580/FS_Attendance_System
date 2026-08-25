<?php

namespace App\Filament\Resources\ClassLevels\Pages;

use App\Filament\Resources\ClassLevels\ClassLevelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClassLevel extends ViewRecord
{
    protected static string $resource = ClassLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
