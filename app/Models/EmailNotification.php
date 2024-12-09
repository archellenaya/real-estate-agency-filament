<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_template_id', 'subject', 'body', 'recipient', 'sent', 'date_sent', 'attachments'
    ];

    public function email_template()
    {
        return $this->belongsTo('App\Models\EmailTemplate', 'email_template_id', 'id');
    }
}