<?php

namespace App\Components\Services\Impl;

use Carbon\Carbon;
use App\Constants\Http\StatusCode;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProcessException;
use App\Components\Passive\Utilities;
use App\Components\Passive\TokenGenerator;
use App\Components\Services\IUniqueLinkService;
use App\Constants\Exception\ProcessExceptionMessage;
use App\Components\Repositories\IUniqueLinkRepository;

class UniqueLinkService implements IUniqueLinkService
{
    private $_uniqueLinkRepository;

    public function __construct(
        IUniqueLinkRepository $uniqueLinkRepository
    )
    {
        $this->_uniqueLinkRepository = $uniqueLinkRepository;
    }

    public function createUniqueLink($user_id, $type)
    {
        $code = $this->generateUniqueCode();

        $expiry_at = Utilities::generateExpiryAt()->format("Y-m-d H:i:s");

        $link_type = $this->getUniqueLinkTypeByType($type);

        return $this->_uniqueLinkRepository->createCode($code, $expiry_at, $link_type->id, $user_id);
    }

    public function getValidUniqueLinkForUser($user, $type)
    {
        $link_type = $this->getUniqueLinkTypeByType($type);
      
        return $this->_uniqueLinkRepository->getUniqueLinkByUserIdAndType(
          $user->id,
          $link_type->id
        );
    }

    public function getByCode($code)
    {
        $unique_link = $this->_uniqueLinkRepository->getByCode($code);

        if (empty($unique_link))  {
            throw new ProcessException(
                ProcessExceptionMessage::CODE_IS_INVALID,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $unique_link;
    }

    public function getValidUniqueLinkByCode($code)
    {
        $uniqueLink = $this->_uniqueLinkRepository->getValidUniqueLinkByCode($code);
        
        if (empty($uniqueLink)) {
            throw new ProcessException(
                ProcessExceptionMessage::CODE_IS_INVALID,
                StatusCode::HTTP_BAD_REQUEST
            );
        }

        return $uniqueLink;
    }

    public function processUniqueLink($code)
    {
        return $this->_uniqueLinkRepository->processUniqueLink($code);
    }

    public function getUniqueLinkTypeByType($type)
    {
        return $this->_uniqueLinkRepository->getUniqueLinkTypeByType($type);
    }

    public function getUniqueLinkTypeById($id)
    {
        return $this->_uniqueLinkRepository->getUniqueLinkTypeById($id);
    }

    public function invalidateUniqueLinkCode($uniqe_link)
    {
        $uniqe_link->date_expiry = Carbon::now();
        $uniqe_link->save();
    }

    private function generateUniqueCode()
    {
        do {
            $code = TokenGenerator::generateUniqueLinkCode();
            $duplicate_code = $this->_uniqueLinkRepository->getByCode($code);

        } while(!empty($duplicate_code));

        return $code;
    }
}
