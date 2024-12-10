<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Exceptions\ProcessException;
use App\Components\Services\IWishlistService;
use App\Components\Validators\IWishlistValidator;

class WishlistController extends BaseController
{
    private $_wishlistService;
    private $_wishlistValidator;

    public function __construct(
        IWishlistValidator $wishlistValidator,
        IWishlistService $wishlistService
    ) {
        $this->_wishlistValidator = $wishlistValidator;
        $this->_wishlistService = $wishlistService;
    }


    public function store(Request $request)
    {

        $reference = $request->property_ref;

        $validator = $this->_wishlistValidator->validateSavingFavoriteProperty([
            'property_ref' => $reference,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {

            $this->_wishlistService->addToList($reference);
        } catch (ProcessException $e) {

            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('Property added successfully!');
    }

    public function remove(Request $request)
    {

        $reference = $request->property_ref;

        $validator = $this->_wishlistValidator->validateSavingFavoriteProperty([
            'property_ref' => $reference,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {

            $this->_wishlistService->removeToList($reference);
        } catch (ProcessException $e) {

            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('Property removed successfully!');
    }

    public function lists(Request $request)
    {
        try {
            $get_alerts_status = ((int)$request->get('get_alerts_status')) ?? 0;
            if ($get_alerts_status === 1) {
                $properties = $this->_wishlistService->getList(true);
            } else {
                $properties = $this->_wishlistService->getList();
            }
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonDataResponse($properties);
    }


    public function updatePropertyAlert(Request $request)
    {
        $validator = $this->_wishlistValidator->validateUpdatePropertyAlert([
            'propertyRef'   => $request->get('propertyRef'),
            'alertOn'       => $request->get('alertOn'),
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        $user = auth()->user();
        if (empty($user) === true) {
            return $this->setValidationErrorJsonResponse(['user' => 'invalid user'], 403);
        }

        $property = Property::where('property_ref_field', $request->get('propertyRef'))
            ->whereHas('user_favorite', function ($user_favorite) use ($user) {
                $user_favorite->where('user_property.user_id', $user->id);
            })->first();

        if (empty($property) === true) {
            return $this->setValidationErrorJsonResponse(['propertyRef' => 'Property is not in user wishlist'], 403);
        }

        $alert_on = $request->get('alertOn') == 1 ? true : false;

        $this->_wishlistService->updatePropertyAlert($user, $property, $alert_on);

        return $this->setJsonDataResponse(['success' => 1]);
    }

    public function clearList()
    {
        try {

            $this->_wishlistService->clearList();
        } catch (ProcessException $e) {

            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->setJsonMessageResponse('Wishlist has been cleared successfully!');
    }

    public function inList(Request $request, $reference)
    {
        $property_reference = $reference;

        $validator = $this->_wishlistValidator->validateSavingFavoriteProperty([
            'property_ref' => $property_reference,
        ]);

        if ($validator->fails()) {
            return $this->setValidationErrorJsonResponse($validator->errors());
        }

        try {

            $booleanCheck = $this->_wishlistService->inList($property_reference);

            return $this->setJsonDataResponse($booleanCheck);
        } catch (ProcessException $e) {

            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }
    }
}
