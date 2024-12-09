<?php 

namespace App\Models\DTO;

class SavedSearchDTO
{
    public $id;
    public $name;
    public $url;
    public $alerts;
    public $email_frequency_id;
    public $email_frequency;

    public function __construct(
        $id,
        $name,
        $url,
        $alerts,
        $email_frequency_id,
        $email_frequency
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
        $this->alerts = $alerts;
        $this->email_frequency_id = $email_frequency_id;
        $this->email_frequency = $email_frequency;
    }
}