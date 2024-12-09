<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IUniqueLinkRepository;
use App\Models\UniqueLink;
use App\Models\UniqueLinkType;
use Carbon\Carbon;

class UniqueLinkRepository implements IUniqueLinkRepository
{
    public function createCode(
        $code, 
        $date_expiry, 
        $link_type_id, 
        $user_id
    ) {
        return UniqueLink::create([
            'code' => $code,
            'date_expiry' => $date_expiry,
            'link_type_id' => $link_type_id,
            'user_id' => $user_id,
        ]);
    }

    public function getByCode($code)
    {
        return UniqueLink::where('code', $code)->first();
    }

    public function getValidUniqueLinkByCode($code)
    {
        return UniqueLink::where('code', $code)
            ->whereNull('date_processed')
            ->where('date_expiry', '>', Carbon::now())
            ->first();
    }

    public function processUniqueLink($code)
    {
        return UniqueLink::where('code', $code)
            ->whereNull('date_processed')
            ->where('date_expiry', '>', Carbon::now())
            ->update([
                'date_processed' => Carbon::now()
            ]);
    }

    public function getUniqueLinkTypeByType($type)
    {
        return UniqueLinkType::where('type', $type)->first();
    }

    public function getUniqueLinkTypeById($id)
    {
        return UniqueLinkType::find($id);
    }

    public function getUniqueLinkByUserIdAndType($user_id, $link_type_id)
    {
        return UniqueLink::where('user_id', $user_id)
            ->where('link_type_id', $link_type_id)
            ->whereNull('date_processed')
            ->where('date_expiry', '>', Carbon::now())
            ->first();
    }
}