<?php

namespace App\Components\Services\Impl;

use App\Components\Repositories\IPropertyAlertRepository;
use App\Components\Services\IPropertyAlertService;
use App\Components\Services\ITypeService;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;
use App\Models\DTO\PropertyAlertDTO;

class PropertyAlertService implements IPropertyAlertService
{
    private $_propertyAlertRepository;
    private $_typeService;

    public function __construct(
        IPropertyAlertRepository $propertyAlertRepository,
        ITypeService $typeService
    )
    {
        $this->_propertyAlertRepository = $propertyAlertRepository;
        $this->_typeService = $typeService;
    }

    public function getPropertyAlert($id)
    {
        $propertyAlert = $this->_propertyAlertRepository->getById($id);

        if (empty($propertyAlert)) {
            throw new ProcessException(
                ProcessExceptionMessage::PROPERTY_ALERT_DOES_NOT_EXIST,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $propertyAlert;
    }

    public function createNewPropertyAlert(
        $name,
        $type_name,
        $property_type_id = [],
        $location_id = [],
        $min_price,
        $max_price
    )
    {
        $type = '';

        if (!empty($type_name)) {
            $type = $this->_typeService->getByName($type_name);
        }

        $propertyAlert = $this->_propertyAlertRepository->createPropertyAlert(
            $name,
            $type->id ?? null,
            $property_type_id ? join(',', $property_type_id) : '',
            $location_id ? join(',', $location_id) : '',
            $min_price,
            $max_price
        );

        return $this->getPropertyAlertDTO($propertyAlert->id);
    }

    public function getPropertyAlertDTO($id)
    {
        $propertyAlert = $this->getPropertyAlert($id);

        return new PropertyAlertDTO(
            $propertyAlert->id,
            $propertyAlert->name,
            $propertyAlert->type->name ?? '',
            $propertyAlert->property_type_id,
            $propertyAlert->location_id,
            $propertyAlert->min_price,
            $propertyAlert->max_price
        );
    }

    public function getAllPropertyAlerts($per_page)
    {
        return $this->_propertyAlertRepository->getAll($per_page);
    }

    public function updatePropertyAlert(
        $id,
        $name,
        $type_name,
        $property_type_id = [],
        $location_id = [],
        $min_price,
        $max_price
    )
    {
        $propertyAlert = $this->getPropertyAlert($id);

        $type = '';

        if (!empty($type_name)) {
            $type = $this->_typeService->getByName($type_name);
        }

        $this->_propertyAlertRepository->updatePropertyAlert(
            $id,
            $name,
            $type->id ?? null,
            join(',', $property_type_id),
            join(',', $location_id),
            $min_price,
            $max_price
        );

        return $this->getPropertyAlertDTO($propertyAlert->id);
    }

    public function deletePropertyAlert($id)
    {
        $propertyAlert = $this->getPropertyAlert($id);

        $propertyAlert->delete();
    }
}