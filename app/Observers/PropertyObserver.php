<?php

namespace App\Observers;

use App\PropertyAudit;
use App\Models\Property;
use Illuminate\Support\Facades\Log;

class PropertyObserver
{
    public function saving($model)
    {
        //
    }

    public function saved($model)
    {
        //
    }


    public function updated(Property $property)
    {
        Log::info('Observer triggered for property ID: ' . $property->id);
        try {
            $updates_in_model = $this->getUpdatesInModel($property);
            Log::info(print_r($updates_in_model));
            if (empty($updates_in_model) === true) {
                return;
            }

            PropertyAudit::create([
                'event_type'   => 'update',
                'property_id' => $property->id,
                'changes'     => json_encode($updates_in_model)
            ]);
        } catch (\Exception $e) {
            Log::error("Error when creating PropertyAudit, exception message: " . $e->getMessage());
        }
    }


    public function getUpdatesInModel($model)
    {
        $changes = [];

        foreach ($model->getOriginal() as $key => $originalValue) {
            $hasChanges     = false;
            $updatedValue   = $model[$key];

            if (is_object($updatedValue)) {
                //convert carbon dates to strings
                if (get_class($updatedValue) === 'Carbon\Carbon') {
                    $updatedValue = $updatedValue->format('Y-m-d H:i:s');
                } else {
                    //unknown object type. We are not interested in such changes.
                    continue;
                }
            }

            if (is_bool($updatedValue) === true) {
                //cast booleans to strings, so they can be easily compared
                $updatedValue = $updatedValue === true ? '1' : '0';
            }

            if (is_numeric($originalValue) && is_numeric($updatedValue)) {
                //comparing numeric values
                if (((float) $originalValue) !== ((float) $updatedValue)) {
                    $hasChanges     = true;
                    $updatedValue   = (float) $updatedValue;
                    $originalValue  = (float) $originalValue;
                }
            } else if ((string)$originalValue !== (string)$updatedValue) {
                //comparing two strings
                $hasChanges = true;
            }

            if ($hasChanges === true) {
                $changes[] = [
                    'field'  => $key,
                    'org'    => (string)$originalValue,
                    'update' => (string)$updatedValue
                ];
            }
        }

        return $changes;
    }
}
