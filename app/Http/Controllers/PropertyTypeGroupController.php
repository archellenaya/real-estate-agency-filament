<?php

namespace App\Http\Controllers;

use App\Models\PropertyTypeGroup;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use App\Niu\Transformers\PropertyTypeTransformer;
use Illuminate\Support\Facades\Log;

class PropertyTypeGroupController extends ApiController
{ 
    protected $propertyTypeTransformer;
    
    public function __construct( PropertyTypeTransformer $propertyTypeTransformer ) {
	 
		$this->propertyTypeTransformer = $propertyTypeTransformer;
	}

    public function index(Request $request)
    {
         // $not_by_group = false or 0  >>$propertyTypes = Property Group
        // $not_by_group = true or 1  >>$propertyTypes = Property SubType 
        $not_by_group  = $request->get( 'not-by-group' );
        $is_commercial  = $request->get( 'is-commercial' );
        $data=[];
        $model_data=null;
        if(!empty($not_by_group)) {
            if(isset($is_commercial)) { 
                $model_data = PropertyType::whereHas('property_type_group', function ($query) use ($is_commercial){
                    $query->where('commercial', $is_commercial);
                })->get();
            }  
            if(empty($model_data) || (isset($model_data) && $model_data->count()==0)) {
                $model_data = PropertyType::all();
            } 
            foreach($model_data as $row) { 
                $data[] = [
                    'id' => $row->id,
                    'code' =>  $row->code,
                    'description' =>  $row->description,
                ];
            } 
        } else {
            
            if(isset($is_commercial)) {
                $model_data = PropertyTypeGroup::whereHas('property_types')->where('commercial', $is_commercial)->get();
            }
            if(empty($model_data) || (isset($model_data) && $model_data->count()==0)) {
                $model_data = PropertyTypeGroup::whereHas('property_types')->get();
            }  

            foreach($model_data as $row) { 
                $data[] = [
                    'id' => $row->id,
                    'code' =>  $row->code,
                    'description' =>  $row->description
                ];
            } 
        }

     
        return $this->setJsonDataResponse( $data );
    }
}