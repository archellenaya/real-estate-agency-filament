<?php

namespace App\Components\Validators\Impl;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Components\Validators\IAccountValidator;

class AccountValidator implements IAccountValidator
{
   public function validateFirstUpdateAccount($data)
   {
      $rules = [
         "buyer_type_id"   => "required",
         "interest_id"     => "nullable",
         "currency"        => "required",
         "send_updates"    => "required",
      ];

      $messages = [];

      return Validator::make($data, $rules, $messages);
   }

   public function validateUpdateAccount($data)
   {
      $rules = [
         'prefix'                => 'nullable|string|max:5',
         'first_name'            => 'required',
         'last_name'             => 'nullable|string',
         'region'                => 'nullable|string',
         'country'               => 'nullable|string',
         'prefix_contact_number' => 'nullable|string',
         'contact_number'        => 'nullable|string',
         "buyer_type_id"         => 'nullable|integer|exists:buyer_types,id',
         "interest_id"           => 'nullable|integer|exists:interests,id',
         "currency"              => 'nullable|string|size:3',
         'profile_image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048|dimensions:min_width=100,min_height=100',
      ];

      $messages = [
         'prefix.string'                  => 'The prefix must be a valid string.',
         'prefix.max'                     => 'The prefix may not be greater than 5 characters.',
         'first_name.required'            => 'The first name is required.',
         'last_name.string'               => 'The last name must be a string.',
         'region.string'                  => 'The region must be a valid string.',
         'country.string'                 => 'The country must be a valid string.',
         'prefix_contact_number.string'   => 'The prefix for the contact number must be a valid string.',
         'contact_number.string'          => 'The contact number must be a valid string.',
         'buyer_type_id.integer'          => 'The buyer type must be a valid integer.',
         'buyer_type_id.exists'           => 'The selected buyer type is invalid.',
         'interest_id.integer'            => 'The interest ID must be a valid integer.',
         'interest_id.exists'             => 'The selected interest ID is invalid.',
         'currency.string'                => 'The currency must be a valid string.',
         'currency.size'                  => 'The currency code must be exactly 3 characters.',
         'profile_image.image'            => 'The profile image must be a valid image file.',
         'profile_image.mimes'            => 'The profile image must be a file of type: jpeg, png, jpg, gif, svg.',
         'profile_image.max'              => 'The profile image must not exceed 2 MB in size.',
         'profile_image.dimensions'       => 'The profile image must be at least 100x100 pixels.',
      ];

      return Validator::make($data, $rules, $messages);
   }

   public function validateDeactivationAccount($data)
   {
      $rules = [
         'password'            => 'required',
      ];

      $messages = [];

      return Validator::make($data, $rules, $messages);
   }

   public function validateAccountEmailChange($data)
   {
      $rules = [
         "email" => "required|email:rfc,filter|max:255|unique:users",
         "password" => "required|min:8",
      ];

      $messages = [
         'email.required' => 'The email is required.',
         'email.email' => 'The email must be a valid email address.',
         'email.max' => 'The email may not be greater than 255 characters.',
         'email.unique' => 'The email has already been taken.',

         'password.required' => 'The password is required.',
         'password.min' => 'The password must be at least 8 characters long.',
      ];

      return Validator::make($data, $rules, $messages);
   }

   public function validateNotificationChange($data)
   {

      $rules = [
         'notify_property_price_change'   => 'required',
         'notify_property_sold'           => 'required',
      ];

      $messages = [];

      return Validator::make($data, $rules, $messages);
   }
}
