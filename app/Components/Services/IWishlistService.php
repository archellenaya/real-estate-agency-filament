<?php

namespace App\Components\Services;

use App\Models\Property;
use App\Models\User;

interface IWishlistService
{
    public function addToList( $reference );

    public function removeToList( $reference );

    public function getList(bool $get_alert_status=false);

    public function clearList();

    public function inList($reference);

    public function updatePropertyAlert(User $user, Property $reference,bool $alert_on);
}