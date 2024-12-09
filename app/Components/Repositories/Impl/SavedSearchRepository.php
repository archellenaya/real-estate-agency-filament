<?php

namespace App\Components\Repositories\Impl;

use App\Components\Repositories\ISavedSearchRepository;
use App\Models\SavedSearch;

class SavedSearchRepository implements ISavedSearchRepository
{
    public function getById($id) 
    {
        return SavedSearch::find($id);
    }


    // public function getByUrlUser(string $url,int $userId){
    //     //function converts url in param to JSON as values are saved in json
    //     $decoded_url = null;
    //     parse_str($url,$decoded_url);
    //     $json_encoded_url = json_encode($decoded_url);
    //     return SavedSearch::where('url',$json_encoded_url)->where('user_id',$userId)->first();
    // }

    public function getByUrlUser(string $url,int $userId) 
    {
        if(empty($url)){
            return null;
        }

        $decoded_url = null;
        parse_str($url,$decoded_url);
        if(empty($decoded_url)){
            return null;
        }
        unset($decoded_url['pg']);
        unset($decoded_url['sort']);

        $query = SavedSearch::where('user_id',$userId);
        foreach($decoded_url as $key => $value){
            if(empty($value) === true){
                continue;
            }
            if(is_array($value)){
                foreach($value as $array_value){
                    if(empty($array_value) === true || is_string($array_value) === false ){
                        continue;
                    }

                    $query
                        ->whereRaw("(JSON_CONTAINS(`saved_searches`.url,'{\"$key\":\"".$array_value."\"}'))");
                }
            }else if(is_string($value)){
                $query
                    ->whereRaw("JSON_CONTAINS(`saved_searches`.url, '{\"$key\":\"$value\"}' )");
            }
        }

        return $query->first();
    }
    
    public function createSaveSearch(
        $name,
        $user_id,
        $url,
        $alerts,
        $email_frequency_id
    ) 
    {
        return SavedSearch::create([
            'name' => $name,
            'user_id' => $user_id,
            'url'  => $url,
            'alerts' => $alerts,
            'email_frequency_id' => $email_frequency_id
        ]);
    }

    public function getAll(int $per_page,int $user_id,?int $force_page=null)
    {
        $query = SavedSearch::leftJoin('email_frequencies as ef', 'ef.id', 'saved_searches.email_frequency_id')
            ->select([
                'saved_searches.id',
                'saved_searches.name',
                'saved_searches.url',
                'saved_searches.alerts',
                'ef.id as email_frequency_id',
                'ef.name as email_frequency',
                'saved_searches.created_at',
                'saved_searches.updated_at',
            ])
            ->where('saved_searches.user_id',$user_id);

        if($force_page !== null){
            return $query->paginate($per_page, ['*'], 'page', $force_page ?? 1);
        }else{
            return $query->paginate($per_page);
        }
    }

    public function updateSaveSearch(
        $id,
        $name,
        $url,
        $alerts,
        $email_frequency_id
    ) 
    {
        return SavedSearch::where('id', $id)->update([
            'name' => $name,
            'url' => $url,
            'alerts' => $alerts,
            'email_frequency_id' => $email_frequency_id
        ]);
    }
}
