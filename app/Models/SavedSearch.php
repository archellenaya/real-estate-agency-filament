<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SavedSearch extends Model
{
    protected $fillable = [
        'name',
        'url',
        'user_id',
        'alerts',
        'email_frequency_id'
    ];

    public function type()
    {
        return $this->belongsTo('App\Models\Type', 'type_id', 'id');
    }

    public function email_frequency()
    {
        return $this->belongsTo('App\Models\EmailFrequency', 'email_frequency_id', 'id');
    }


    public function user(){
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function getUrlAttribute(){
        $url = $this->getRawOriginal('url');
        if(empty($url)){
            return $url;
        }
        $json_url       = json_decode($url);
        $encoded_url    = http_build_query($json_url);

        return $encoded_url;
    }
}