<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAuditsTracker extends Model
{

    public $table       = 'properties_audits_tracker';
    public $timestamps  = false;

    protected $fillable = [
        'data',
        'last_executed_on'
    ];

    /**
     * Gets the last record based on last_executed_on in the DB
     *
     * @return PropertyAuditsTracker|null
     */
    public static function getLastTrackedPropertyAudit(){
        return static::orderBy('last_executed_on','desc')->first();
    }
}