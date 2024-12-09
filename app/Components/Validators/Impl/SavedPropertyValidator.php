<?php

namespace App\Components\Validators\Impl;

use App\Components\Validators\ISavedPropertyValidator;
use Illuminate\Support\Facades\Validator;

class SavedPropertyValidator implements ISavedPropertyValidator
{

    public function validateSendEmailMoreInfo($data)
    {
        $rules = [
            'message'                       => 'required|max:2000',
            'property_ids'                  => 'nullable|array',
            'tracking_agent_branch_email'   => 'nullable|email|max:225',
            'tracking_agent_email'          => 'nullable|email|max:225',
            'tracking_agent_name'           => 'nullable|max:225',
        ];

        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

    public function validateShareSavedProperty($data)
    {
        // Define initial validation rules
        $rules = [
            'message' => 'required|max:2000',
            'recipients' => 'required|string', // Validate as a string initially
        ];

        $messages = [
            'message.required' => 'The message field is required.',
            'message.max' => 'The message may not be greater than 2000 characters.',
            'recipients.required' => 'The recipients field is required.',
            'recipients.string' => 'The recipients must be a string.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return $validator;
        }

        $emailArray = array_map('trim', explode(',', $data['recipients']));

        $emailRules = [
            'recipients' => 'required|array',
            'recipients.*' => 'required|email', // Validate each email
        ];
     
        $data['recipients'] = $emailArray;

        return Validator::make($data, $emailRules, $messages);
    }
}
