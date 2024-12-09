<?php

namespace App\Components\Services;

interface ISavedPropertyService
{
    public function sendEmailForMorePropertiesInfo($message, $properties = [],$properties_to_visit = [],$username,?string $tracking_agent_branch_email=null,?string $tracking_agent_email=null,?string $tracking_agent_name=null);

    public function shareSavedProperties($message, $recipients);
}