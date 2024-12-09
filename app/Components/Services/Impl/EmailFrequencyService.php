<?php 

namespace App\Components\Services\Impl;

use App\Components\Repositories\IEmailFrequencyRepository;
use App\Components\Services\IEmailFrequencyService;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Constants\Http\StatusCode;
use App\Exceptions\ProcessException;

class EmailFrequencyService implements IEmailFrequencyService
{
    private $_emailFrequencyRepository;

    public function __construct(
        IEmailFrequencyRepository $emailFrequencyRepository
    )
    {
        $this->_emailFrequencyRepository = $emailFrequencyRepository;
    }

    public function getById($id)
    {
        $email_frequency = $this->_emailFrequencyRepository->getById($id);

        if (empty($email_frequency)) {
            throw new ProcessException(
                ProcessExceptionMessage::EMAIL_FREQUENCY_DOES_NOT_EXIST,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $email_frequency;
    }

    public function getByName($name)
    {
        $email_frequency = $this->_emailFrequencyRepository->getByName($name);

        if (empty($email_frequency)) {
            throw new ProcessException(
                ProcessExceptionMessage::EMAIL_FREQUENCY_DOES_NOT_EXIST,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $email_frequency;
    }

    public function getAll()
    {   
        return $this->_emailFrequencyRepository->getAll();
    }
}