<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (array_key_exists('roles', $data)) {
            $data['roles'] = UserResource::sanitizeRoleIds($data['roles']);
        }

        return $data;
    }
}
