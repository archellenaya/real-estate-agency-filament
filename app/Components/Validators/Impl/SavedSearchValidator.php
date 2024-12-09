<?php

namespace App\Components\Validators\Impl;

use App\Components\Validators\ISavedSearchValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SavedSearchValidator implements ISavedSearchValidator
{

    public function __construct(){
        //custom validation rule to make sure there is no other Saved search that has the same user id and url values
        //more info at: https://stackoverflow.com/questions/26683762/how-to-add-combined-unique-fields-validator-rule-in-laravel-4

        Validator::extend('unique_multiple', function ($attribute, $value, $parameters, $validator)
        {
             // Get the other fields
             $fields = $validator->getData();
        
             // Get table name from first parameter
             $table = array_shift($parameters);
        
            // Build the query
            $query = DB::table($table);
        
            // Add the field conditions
            foreach ($parameters as $i => $field) {
                $query->where($field, $fields[$field]);
            }
        
            // Validation result will be false if any rows match the combination
            return ($query->count() == 0);
        });
    }

    public function validateSavedSearchId($data) 
    {
        $rules = [
            'id' => 'required|integer'
        ];
     
        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

    public function validateStoreSavedSearch($data) 
    {
        $rules = [
            'name' => 'required|max:100',
            'type' => 'nullable',
            'user_id' => 'required|integer',
            'url'  => 'required|unique_multiple:saved_searches,user_id,url',
            // 'property_type_id' => 'nullable|array',
            // 'location_id' => 'nullable|array',
            // 'min_price' => 'nullable|numeric',
            // 'max_price' => 'nullable|numeric',
            'alerts' => 'nullable|in:0,1',
            'email_frequency_id' => 'nullable|integer'
        ];
     
        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

    public function validateUpdateSavedSearch($data) 
    {
        $rules = [
            'id' => 'required|integer',
            'name' => 'required|max:100',
            'type' => 'nullable',
            'url'  => 'required',
            'alerts' => 'nullable|in:0,1',
            'email_frequency_id' => 'nullable|integer'
        ];
     
        $messages = [];

        return Validator::make($data, $rules, $messages);
    }
}