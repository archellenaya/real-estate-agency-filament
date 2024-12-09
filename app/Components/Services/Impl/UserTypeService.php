<?php

namespace App\Components\Services\Impl;

use App\Components\Repositories\IUserTypeRepository;
use App\Components\Services\IUserTypeService;
use App\Constants\Http\StatusCode;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Exceptions\ProcessException;

class UserTypeService implements IUserTypeService
{
    private $_userTypeRepository;

    public function __construct(
        IUserTypeRepository $userTypeRepository
    )
    {
        $this->_userTypeRepository = $userTypeRepository;
    }

    public function getUserTypeById($id) 
    {
        $user_type = $this->_userTypeRepository->getUserTypeById($id);

        if (empty($user_type)) {
			throw new ProcessException(
				ProcessExceptionMessage::USER_TYPE_DOES_NOT_EXIST,
				StatusCode::HTTP_BAD_REQUEST
			);
		}

		return $user_type;
    }

    public function getUserTypeByType($type) 
    {
        $user_type = $this->_userTypeRepository->getUserTypeByType($type);

        if (empty($user_type)) {
			throw new ProcessException(
				ProcessExceptionMessage::USER_TYPE_DOES_NOT_EXIST,
				StatusCode::HTTP_BAD_REQUEST
			);
		}

		return $user_type;
    }

    public function getAll() 
    {
        return $this->_userTypeRepository->getAll();
    }
}
