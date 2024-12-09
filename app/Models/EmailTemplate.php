<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'notification_trigger_id', 'subject', 'body', 'attachments'
    ];

    public function notification_trigger()
    {
        return $this->belongsTo('App\Models\NotificationTrigger', 'notification_trigger_id', 'id');
    }

    public function email_notifications()
    {
        return $this->hasMany('App\Models\EmailNotification', 'email_template_id', 'id');
    }
}