<?php 

namespace App\Components\Services;

interface IUniqueLinkService
{
    public function createUniqueLink($user_id, $type);

    public function getByCode($code);

    public function getValidUniqueLinkByCode($code);
    
    public function getValidUniqueLinkForUser($user, $type);

    public function processUniqueLink($code);

    public function getUniqueLinkTypeByType($type);

    public function getUniqueLinkTypeById($id);

    public function invalidateUniqueLinkCode($uniqe_link);
}