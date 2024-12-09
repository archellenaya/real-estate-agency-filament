<?php 

namespace App\Components\Services\Impl;

use App\Components\Repositories\ITypeRepository;
use App\Components\Services\ITypeService;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;

class TypeService implements ITypeService
{
    private $_typeRepository;

    public function __construct(
        ITypeRepository $typeRepository
    )
    {
        $this->_typeRepository = $typeRepository;
    }

    public function getById($id)
    {
        $type = $this->_typeRepository->getById($id);

        if (empty($type)) {
            throw new ProcessException(
                ProcessExceptionMessage::TYPE_DOES_NOT_EXIST,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $type;
    }

    public function getByName($name)
    {
        $type = $this->_typeRepository->getByName($name);

        if (empty($type)) {
            throw new ProcessException(
                ProcessExceptionMessage::TYPE_DOES_NOT_EXIST,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $type;
    }

    public function getAll()
    {   
        return $this->_typeRepository->getAll();
    }
}