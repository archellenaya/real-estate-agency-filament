<?php 

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\IEmailFrequencyRepository;
use App\Models\EmailFrequency;

class EmailFrequencyRepository implements IEmailFrequencyRepository
{
    public function getById($id)
    {
        return EmailFrequency::find($id);
    }

    public function getByName($name)
    {
        return EmailFrequency::where('name', $name)->first();
    }

    public function getAll()
    {
        return EmailFrequency::all();
    }
}