<?php

namespace App\Components\Services;

interface IAccountService
{
    public function firstAccountUpdate($buyer_type_id, $interest_id, $currency, $send_updates);

    public function getProfileAccount();

    public function updateAccount(
        $prefix = NULL,
        $first_name,
        $last_name,
        $region = NULL,
        $country = NULL,
        $prefix_contact_number = NULL,
        $contact_number = NULL,
        $buyer_type_id = NULL,
        $interest_id = NULL,
        $currency = NULL,
        $image_filename = NULL
    );

    public function deactivateAccount($password);

    public function changeAccountEmail($password, $email);

    public function updateNotification($notify_property_price_change, $notify_property_sold);

    public function getNotificationSettings();
}
