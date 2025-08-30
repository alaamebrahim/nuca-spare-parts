<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class BoringAvatarsProvider implements Contracts\AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        return URL::to('/img/icons8-user-80.png');
    }
}