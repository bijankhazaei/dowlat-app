<?php

namespace App\Filament\Resources\PromptResource\Pages;

use App\Filament\Resources\PromptResource;
use App\Services\MehranAI\MehranApiService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Exception;

class CreatePrompt extends CreateRecord
{
    protected static string $resource = PromptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param array $data
     * @return Model
     * @throws Exception
     */
    protected function handleRecordCreation(array $data): Model
    {
        return app(MehranApiService::class)->createPrompt($data);
    }
}
