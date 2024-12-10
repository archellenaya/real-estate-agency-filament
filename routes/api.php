<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BranchController;

use App\Http\Controllers\ConsultantController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\PropertyTypeGroupController;
use App\Http\Controllers\EmailFrequencyController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LocalityController;
use App\Http\Controllers\PartnerController;

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\PropertyAlertsController;
use App\Http\Controllers\PropertyAuditsController;
use App\Http\Controllers\PropertyBlockController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyStatusController;
use App\Http\Controllers\PropertyTypeController;
use App\Http\Controllers\CsrfController;
use App\Http\Controllers\EmailNotificationController;
use App\Http\Controllers\SavedPropertiesController;
use App\Http\Controllers\SavedSearchesController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\WishlistController; 
// use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
// use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
// use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
// use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'v2'], function () use ($router) {
    $router->get('/properties/byRefs',             [PropertyController::class, 'getPropertiesByRefs']);
    $router->get('/property/ref',                  [PropertiesController::class, 'getPropertyByRef']);
    $router->get('/property/{slug}',               [PropertyController::class, 'seoPropertySlug']);
    $router->group(['prefix' => 'seo'], function () use ($router) {
        $router->get('/property/{slug}',         [PropertyController::class, 'seoPropertySearch']);
        $router->get('/metatags',                [PropertyController::class, 'seoPropertyMetatags']);
    });
    $router->group(['prefix' => 'properties'], function () use ($router) {
        $router->get('/xml',    [PropertyController::class, 'getPropertyXML']);
    });

    $router->get('/seo-property-slugs',                 [PropertyController::class, 'getSEOPropertySlugs']);
    $router->get('/seo-inner-property-slugs',               [PropertyController::class, 'getInnerPropertySlugs']);
    $router->get('/seo-categories',                     [PropertyController::class, 'getSEOPropertyCategories']);
    Route::group(['middleware' => 'auth.very_basic'], function ()  use ($router) {

        $router->group(['prefix' => 'email-notifications'], function () use ($router) {
            $router->get('/',                                   [EmailNotificationController::class, 'index']);
        });

        $router->get('/cache-clear',                        [PropertyController::class, 'cacheClear']);
        $router->post('search',                             [SearchController::class, 'search']);
        $router->post('cron',                               [SearchController::class, 'getLatest']);
        $router->post('quickSearch',                        [SearchController::class, 'quickSearch']);
        $router->get('/audits/properties',                  [PropertyAuditsController::class, 'index']);
        $router->get('/property-cf-clear/{reference}',      [PropertyController::class, 'clearPropertyCache']);
        $router->get('/seo-categories/{slug}',              [PropertyController::class, 'getSEOPropertyCategory']);
        $router->post('/property-exist',                    [PropertyController::class, 'checkProperty']);
    });

    Route::middleware(['web'])->group(function () {
        Route::get('/auth/{provider}/redirect',           [LoginController::class, 'redirectToProvider']);
    });

    $router->get('/property/video/{filename}',                  [VideoController::class, 'getPropertyVideoRedirection']);
});

Route::group(['prefix' => 'v1'], function () use ($router) {
    $router->group(['prefix' => 'properties'], function () use ($router) {
        $router->get('/search',         [PropertyController::class, 'search']);
        $router->get('/price_update',   [PropertyController::class, 'priceUpdate']);
        $router->get('/regions',        [PropertyController::class, 'regions']);
    });

    $router->group(['prefix' => 'branches'], function () use ($router) {
        $router->get('/',               [BranchController::class, 'index']);
        $router->get('/{id}',           [BranchController::class, 'view']);
    });

    $router->get('/consultants',                           [ConsultantController::class, 'index']);
    $router->get('/consultants/code',                      [ConsultantController::class, 'getConsultantByCode']);
    $router->get('/consultants/{id}/properties',           [PropertiesController::class, 'index']);
    $router->get('/consultants/{consultants}',             [ConsultantController::class, 'show']);
    $router->get('/consultant/{consultants}/properties',   [PropertyController::class, 'consultantProperty']);
    $router->get('/agents',                                [AgentController::class, 'search']);
    $router->get('/property_subtypes/{commercial?}',       [PropertyTypeController::class, 'index']);
    $router->get('/propertytypes',                         [PropertyTypeGroupController::class, 'index']);
    $router->get('/property_status',                       [PropertyStatusController::class, 'index']);
    $router->get('/features',                              [FeaturesController::class, 'index']);
    $router->get('/localities',                            [LocalityController::class, 'index']);
    $router->get('/file/{id}',                             [FileController::class, 'getFile']);
    $router->get('/properties/list',                       [PropertiesController::class, 'listProperties']);
    $router->get('/properties/{id}/features',              [FeaturesController::class, 'index']);
    $router->get('/properties/{id}/files',                 [FileController::class, 'index']);
    $router->get('/properties',                            [PropertiesController::class, 'index']);
    $router->get('/properties/{properties}',               [PropertiesController::class, 'show']);
    $router->get('/countries',                             [CountryController::class, 'index']);

    $router->group(['prefix' => 'projects'], function () use ($router) {
        $router->get('/',             [ProjectController::class, 'index']);
        $router->get('/{id}/image',   [ProjectController::class, 'getPhoto']);
    });

    $router->get('/price_update',   [PropertyController::class, 'priceUpdate']);
    $router->group(['middleware' => 'auth.very_basic'], function ()  use ($router) {
        $router->get('/files/{files}',                           [FileController::class, 'destroy']);
        //begin api
        $router->group(['prefix' => '/form'], function () use ($router) {
            $router->post('/{slug}',                        [FormController::class, 'processForm']);
            $router->get('/{slug}/lists',                   [FormController::class, 'getForms']);
        });
        // $router->group(['namespace' => '\Rap2hpoutre\LaravelLogViewer'], function () use ($router) {
        //     $router->get('logs', 'LogViewerController@index');
        // })
    });

    $router->group(['prefix' => '/image'], function () use ($router) {
        $router->get('/consultant/{filename}',                  [ImageController::class, 'getConsultantImage']);
        $router->get('/property/{type}/{filename}',             [ImageController::class, 'getPropertyImage']);
        $router->get('/{filename}',                             [ImageController::class, 'getPublicImage']);
        $router->get('/user/{filename}',                        [ImageController::class, 'getUserImage']);
    });

    $router->group(['prefix' => '/auth'], function () use ($router) {
        $router->post('/login',                             [LoginController::class, 'login']);
        $router->post('/register',                          [RegisterController::class, 'register']);
        $router->post('/{provider}/login',                  [LoginController::class, 'socialLogin']);

        $router->post('/resend',                            [RegisterController::class, 'resendVerificationEmail']);
        $router->post('/verify',                            [RegisterController::class, 'verifyRegistration']);
        $router->post('/verify/set',                        [RegisterController::class, 'verifyRegistrationAndSetPassword']);

        $router->group(['prefix' => 'password'], function () use ($router) {
            $router->post('/email',                 [ResetPasswordController::class, 'sendPasswordReset']);
            $router->post('/reset',                 [ResetPasswordController::class, 'passwordReset']);
        });
    });

    $router->group(['middleware' => ['auth:sanctum']], function () use ($router) {
        $router->group(['prefix' => '/auth'], function () use ($router) {
            $router->post('/me',                                [UserController::class, 'getLoggedInUserDetails']);
            $router->post('/refresh_token',                     [LoginController::class, 'refreshToken']);
            $router->post('/change-password',                   [UserController::class, 'changePassword']);
            $router->post('/logout',                            [LoginController::class, 'logout']);
            $router->post('/change-email',                      [AccountController::class, 'changeAccountEmail']);
        });

        $router->group(['prefix' => '/wish'], function () use ($router) {
            $router->post('/save',                              [WishlistController::class, 'store']);
            $router->post('/remove',                            [WishlistController::class, 'remove']);
            $router->patch('/alert/update',                     [WishlistController::class, 'updatePropertyAlert']);
            $router->delete('/clear_list',                      [WishlistController::class, 'clearList']);
            $router->get('/lists',                              [WishlistController::class, 'lists']);
            $router->get('/{reference}',                        [WishlistController::class, 'inList']);
        });

        $router->group(['prefix' => '/account'], function () use ($router) {
            $router->post('/first-update',                      [AccountController::class, 'updateAccountFirstLog']);
            $router->get('/first-update-display',               [AccountController::class, 'createAccountFirstLog']);
            $router->get('/display',                            [AccountController::class, 'getAccount']);
            $router->post('/update',                            [AccountController::class, 'updateAccount']);
            $router->post('/deactivate',                        [AccountController::class, 'deactivateAccount']);
            $router->post('/notification',                      [AccountController::class, 'setNotification']);
            $router->get('/notification_settings',              [AccountController::class, 'getNotification']);
        });

        $router->group(['prefix' => 'email-frequencies'], function () use ($router) {
            $router->get('/',                                   [EmailFrequencyController::class, 'index']);
            $router->get('/{id}',                               [EmailFrequencyController::class, 'show']);
        });

        $router->group(['prefix' => 'saved-searches'], function () use ($router) {
            $router->get('/',                                   [SavedSearchesController::class, 'index']);
            $router->post('/',                                  [SavedSearchesController::class, 'store']);
            $router->post('/exists',                            [SavedSearchesController::class, 'exists']);
            $router->get('/{id}',                               [SavedSearchesController::class, 'show']);
            $router->patch('/{id}',                             [SavedSearchesController::class, 'update']);
            $router->delete('/{id}',                            [SavedSearchesController::class, 'delete']);
        });

        $router->group(['prefix' => 'property-alerts'], function () use ($router) {
            $router->get('/',                                   [PropertyAlertsController::class, 'index']);
            $router->post('/',                                  [PropertyAlertsController::class, 'store']);
            $router->get('/{id}',                               [PropertyAlertsController::class, 'show']);
            $router->patch('/{id}',                             [PropertyAlertsController::class, 'update']);
            $router->delete('/{id}',                            [PropertyAlertsController::class, 'delete']);
        });

        $router->group(['prefix' => 'saved-properties'], function () use ($router) {
            $router->post('/more-info',                         [SavedPropertiesController::class, 'sendEmail']);
            $router->post('/share',                             [SavedPropertiesController::class, 'shareSavedProperties']);
        });
    });
});
