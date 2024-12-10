<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

/**
 * Class CountryController
 *
 * @package App\Http\Controllers
 */
class CountryController extends ApiController
{
    public function index(Request $request)
    {
        $code = $request->get('country-code');
        $data = [];

        $query = Country::query();

        if ($code) {
            $query->where('code', $code);
        }

        $countries = $query->orderBy('description', 'ASC')->get();

        foreach ($countries as $country) {
            $data[$country->code] = $country->description;
        }

        return $data;
    }
}
