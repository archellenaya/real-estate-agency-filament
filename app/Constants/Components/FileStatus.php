<?php 

namespace App\Constants\Components;

class FileStatus
{
    public const TO_OPTIMIZE = 'to_optimize'; // to feed in python optimizer
    public const TO_OPTIMIZE_PRIO = 'to_optimize_prio'; // to feed in python optimizer
    public const TO_FINALIZE = 'to_finalize'; // after optimize, need to update filename, url_field and status_field  
    public const READY       = 'ready';       // complete ready to display in frontend
    public const FAILED      = 'failed';      // failed to optimize
    public const TO_DELETE   = 'to_delete';      // file will be deleted
}