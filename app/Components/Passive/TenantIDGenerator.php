<?php
 
namespace App\Components\Passive;

use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\UniqueIdentifierGenerator;
use Ramsey\Uuid\Uuid;
use App\Components\Passive\Utilities;

class TenantIDGenerator implements UniqueIdentifierGenerator
{
    public static function generate($resource): string
    { 
        return Utilities::slugify($resource->name);
    }
}
