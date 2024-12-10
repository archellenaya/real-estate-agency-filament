<?php

namespace App\Http\Controllers;

use App\Components\Services\IPropertyAlertService;
use App\Components\Validators\IPropertyAlertValidator;
use App\Exceptions\ProcessException;
use Illuminate\Http\Request;

class PropertyAlertsController extends BaseController
{
    private $_propertyAlertService;
    private $_propertyAlertValidator;
    
    public function __construct(
        IPropertyAlertService $propertyAlertService,
        IPropertyAlertValidator $propertyAlertValidator
    )
    {
        $this->_propertyAlertService = $propertyAlertService;
        $this->_propertyAlertValidator = $propertyAlertValidator;
    }

    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 10;

        return $this->_propertyAlertService->getAllPropertyAlerts($per_page);
    }

    public function store(Request $request)
    {
        $type = $request->type;
        $property_type_id = $request->property_type_id;
        $location_id = $request->location_id;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $name = $request->name;

        $data = [
            'name' => $name,
            'type' => $type,
            'property_type_id' => $property_type_id,
            'location_id' => $location_id,
            'min_price' => $min_price,
            'max_price' => $max_price
        ];

        $validator = $this->_propertyAlertValidator->validateStorePropertyAlert($data);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $propertyAlert = $this->_propertyAlertService->createNewPropertyAlert(
                $name,
                $type,
                $property_type_id,
                $location_id,
                $min_price,
                $max_price
            );
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($propertyAlert); 
    }

    public function show($id)
    {
        $validator = $this->_propertyAlertValidator->validatePropertyAlertId([
            'id' => $id
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $propertyAlert = $this->_propertyAlertService->getPropertyAlertDTO($id);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($propertyAlert); 
    }

    public function update(Request $request, $id)
    {
        $type = $request->type;
        $property_type_id = $request->property_type_id;
        $location_id = $request->location_id;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $name = $request->name;

        $data = [
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'property_type_id' => $property_type_id,
            'location_id' => $location_id,
            'min_price' => $min_price,
            'max_price' => $max_price,
        ];

        $validator = $this->_propertyAlertValidator->validateUpdatePropertyAlert($data);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $propertyAlert = $this->_propertyAlertService->updatePropertyAlert(
                $id,
                $name,
                $type,
                $property_type_id,
                $location_id,
                $min_price,
                $max_price
            );
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($propertyAlert);
    }

    public function delete($id)
    {
        $validator = $this->_propertyAlertValidator->validatePropertyAlertId([
            'id' => $id,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {
            $this->_propertyAlertService->deletePropertyAlert($id);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('success');
    }
}