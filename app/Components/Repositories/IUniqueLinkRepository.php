<?php 

namespace App\Components\Repositories;

interface IUniqueLinkRepository
{
    public function createCode(
        $code, 
        $date_expiry, 
        $link_type_id, 
        $user_id
    );

    public function getByCode($code);

    public function getValidUniqueLinkByCode($code);

    public function processUniqueLink($code);

    public function getUniqueLinkTypeByType($type);

    public function getUniqueLinkTypeById($id);

    public function getUniqueLinkByUserIdAndType($user_id, $link_type_id);
}