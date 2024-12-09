<?php

namespace App\Providers;

use App\Models\File;
use Illuminate\Support\ServiceProvider;
use App\Components\Services\IApiService;
use App\Components\Services\IFormService;
use App\Components\Services\ITypeService;
use App\Components\Services\IUserService;
use Illuminate\Support\Facades\Validator;
use App\Components\Services\IAgentService;
use App\Components\Services\IAPIv5Service;
use App\Components\Services\IEmailService;
use App\Components\Services\IImageService;
use App\Components\Services\IVideoService;
use App\Components\Services\IBranchService;
use App\Components\Services\IAccountService;
use App\Components\Services\ICommandService;
use App\Components\Services\IIPStackService;
use App\Components\Services\Impl\ApiService;
use App\Components\Services\IProfileService;
use App\Components\Services\IProjectService;
use App\Components\Services\IInterestService;
use App\Components\Services\ILandlordService;
use App\Components\Services\ILocalityService;
use App\Components\Services\Impl\TypeService;
use App\Components\Services\Impl\UserService;
use App\Components\Services\IPropertyService;
use App\Components\Services\IUserTypeService;
use App\Components\Services\IWishlistService;
use App\Components\Validators\IUserValidator;
use App\Components\Services\IAgentSyncService;
use App\Components\Services\IBuyerTypeService;
use App\Components\Services\Impl\AgentService;
use App\Components\Services\Impl\APIv5Service;
use App\Components\Services\Impl\EmailService;
use App\Components\Services\Impl\ImageService;
use App\Components\Services\Impl\VideoService;
use App\Components\Services\IBranchSyncService;
use App\Components\Services\ICloudflareService;
use App\Components\Services\Impl\BranchService;
use App\Components\Services\Impl\CareerService;
use App\Components\Services\IUniqueLinkService;
use App\Components\Repositories\IFileRepository;
use App\Components\Repositories\IFormRepository;
use App\Components\Repositories\ITypeRepository;
use App\Components\Repositories\IUserRepository;
use App\Components\Services\Impl\AccountService;
use App\Components\Services\Impl\CommandService;
use App\Components\Services\Impl\IPStackService;
use App\Components\Services\Impl\ProfileService;
use App\Components\Services\Impl\ProjectService;
use App\Components\Services\IProjectSyncService;
use App\Components\Services\ISavedSearchService;
use App\Components\Validators\IAccountValidator;
use App\Components\Repositories\IAgentRepository;
use App\Components\Services\ILocalitySyncService;
use App\Components\Services\Impl\InterestService;
use App\Components\Services\Impl\LandlordService;
use App\Components\Services\Impl\LocalityService;
use App\Components\Services\Impl\PropertyService;
use App\Components\Services\Impl\UserTypeService;
use App\Components\Services\Impl\WishlistService;
use App\Components\Services\IPropertySyncService;
use App\Components\Validators\Impl\UserValidator;
use App\Components\Validators\IWishlistValidator;
use App\Components\Repositories\IBranchRepository;
use App\Components\Services\IAgentPropertyService;
use App\Components\Services\Impl\AgentFormService;
use App\Components\Services\Impl\AgentSyncService;
use App\Components\Services\Impl\AgilisApiService;
use App\Components\Services\Impl\BuyerTypeService;
use App\Components\Services\Impl\ContactUsService;
use App\Components\Services\IPropertyAlertService;
use App\Components\Services\ISavedPropertyService;
use App\Components\Repositories\IProfileRepository;
use App\Components\Repositories\IProjectRepository;
use App\Components\Services\IAuthenticationService;
use App\Components\Services\IEmailFrequencyService;
use App\Components\Services\Impl\BranchSyncService;
use App\Components\Services\Impl\CloudflareService;
use App\Components\Services\Impl\UniqueLinkService;
use App\Components\Repositories\IInterestRepository;
use App\Components\Repositories\ILandlordRepository;
use App\Components\Repositories\ILocalityRepository;
use App\Components\Repositories\Impl\FileRepository;
use App\Components\Repositories\Impl\FormRepository;
use App\Components\Repositories\Impl\TypeRepository;
use App\Components\Repositories\Impl\UserRepository;
use App\Components\Repositories\IPropertyRepository;
use App\Components\Repositories\IUserTypeRepository;
use App\Components\Services\Impl\ProjectSyncService;
use App\Components\Services\Impl\SavedSearchService;
use App\Components\Validators\Impl\AccountValidator;
use App\Components\Validators\ISavedSearchValidator;
use App\Components\Repositories\IBuyerTypeRepository;
use App\Components\Repositories\Impl\AgentRepository;
use App\Components\Services\Impl\LocalitySyncService;
use App\Components\Services\Impl\PropertySyncService;
use App\Components\Validators\Impl\WishlistValidator;
use App\Components\Repositories\Impl\BranchRepository;
use App\Components\Repositories\IUniqueLinkRepository;
use App\Components\Services\IEmailNotificationService;
use App\Components\Services\Impl\AgentPropertyService;
use App\Components\Services\Impl\PropertyAlertService;
use App\Components\Services\Impl\SavedPropertyService;
use App\Components\Validators\IPropertyAlertValidator;
use App\Components\Validators\ISavedPropertyValidator;
use App\Components\Repositories\Impl\ProfileRepository;
use App\Components\Repositories\Impl\ProjectRepository;
use App\Components\Repositories\ISavedSearchRepository;
use App\Components\Services\Impl\AuthenticationService;
use App\Components\Services\Impl\EmailFrequencyService;
use App\Components\Validators\IAuthenticationValidator;
use App\Components\Repositories\Impl\InterestRepository;
use App\Components\Repositories\Impl\LandlordRepository;
use App\Components\Repositories\Impl\LocalityRepository;
use App\Components\Repositories\Impl\PropertyRepository;
use App\Components\Repositories\Impl\UserTypeRepository;
use App\Components\Services\Impl\AgilisAgentSyncService;
use App\Components\Services\ISeoPropertyCategoryService;
use App\Components\Validators\Impl\SavedSearchValidator;
use App\Components\Repositories\IEmailTemplateRepository;
use App\Components\Repositories\Impl\BuyerTypeRepository;
use App\Components\Repositories\IPropertyAlertRepository;
use App\Components\Services\ISocialAuthenticationService;
use App\Components\Repositories\IEmailFrequencyRepository;
use App\Components\Repositories\Impl\UniqueLinkRepository;
use App\Components\Services\Impl\EmailNotificationService;
use App\Components\Validators\Impl\PropertyAlertValidator;
use App\Components\Validators\Impl\SavedPropertyValidator;
use App\Components\Repositories\Impl\SavedSearchRepository;
use App\Components\Services\Impl\AgilisPropertySyncService;
use App\Components\Validators\Impl\AuthenticationValidator;
use App\Components\Services\Impl\SeoPropertyCategoryService;
use App\Components\Repositories\IEmailNotificationRepository;
use App\Components\Repositories\Impl\EmailTemplateRepository;
use App\Components\Repositories\Impl\PropertyAlertRepository;
use App\Components\Services\Impl\SocialAuthenticationService;
use App\Components\Validators\ISocialAuthenticationValidator;
use App\Components\Repositories\Impl\EmailFrequencyRepository;
use App\Components\Repositories\INotificationTriggerRepository;
use App\Components\Repositories\ISeoPropertyCategoryRepository;
use App\Components\Repositories\Impl\EmailNotificationRepository;
use App\Components\Validators\Impl\SocialAuthenticationValidator;
use App\Components\Repositories\Impl\NotificationTriggerRepository;
use App\Components\Repositories\Impl\SeoPropertyCategoryRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot()
    {
        // REPOSITORIES
        $this->app->singleton(IPropertyRepository::class, function ($app) {
            return new PropertyRepository;
        });

        $this->app->singleton(IUserRepository::class, function ($app) {
            return new UserRepository;
        });

        $this->app->singleton(ILandlordRepository::class, function ($app) {
            return new LandlordRepository;
        });

        $this->app->singleton(IUserTypeRepository::class, function ($app) {
            return new UserTypeRepository;
        });

        $this->app->singleton(IBranchRepository::class, function ($app) {
            return new BranchRepository;
        });

        $this->app->singleton(
            IEmailNotificationRepository::class,
            function ($app) {
                return new EmailNotificationRepository;
            }
        );

        $this->app->singleton(IEmailTemplateRepository::class, function ($app) {
            return new EmailTemplateRepository;
        });

        $this->app->singleton(INotificationTriggerRepository::class, function ($app) {
            return new NotificationTriggerRepository;
        });

        $this->app->singleton(IUniqueLinkRepository::class, function ($app) {
            return new UniqueLinkRepository;
        });

        $this->app->singleton(IProfileRepository::class, function ($app) {
            return new ProfileRepository;
        });

        $this->app->singleton(IAPIv5Service::class, function ($app) {
            return new APIv5Service;
        });

        $this->app->singleton(IBuyerTypeRepository::class, function ($app) {
            return new BuyerTypeRepository;
        });

        $this->app->singleton(IInterestRepository::class, function ($app) {
            return new InterestRepository;
        });

        $this->app->singleton(ITypeRepository::class, function ($app) {
            return new TypeRepository;
        });

        $this->app->singleton(IEmailFrequencyRepository::class, function ($app) {
            return new EmailFrequencyRepository;
        });

        $this->app->singleton(ISavedSearchRepository::class, function ($app) {
            return new SavedSearchRepository;
        });

        $this->app->singleton(IPropertyAlertRepository::class, function ($app) {
            return new PropertyAlertRepository;
        });

        $this->app->singleton(IFormRepository::class, function ($app) {
            return new FormRepository(
                $app->make(IEmailNotificationService::class),
            );
        });

        $this->app->singleton(ILocalityRepository::class, function ($app) {
            return new LocalityRepository;
        });

        $this->app->singleton(IAgentRepository::class, function ($app) {
            return new AgentRepository;
        });

        $this->app->singleton(IProjectRepository::class, function ($app) {
            return new ProjectRepository;
        });

        $this->app->singleton(ISeoPropertyCategoryRepository::class, function ($app) {
            return new SeoPropertyCategoryRepository;
        });

        $this->app->singleton(IFileRepository::class, function ($app) {
            return new FileRepository(
                $app->make(File::class)
            );
        });


        // SERVICES
        $this->app->singleton(ISeoPropertyCategoryService::class, function ($app) {
            return new SeoPropertyCategoryService(
                $app->make(ISeoPropertyCategoryRepository::class),
            );
        });

        $this->app->singleton(ILandlordService::class, function ($app) {
            return new LandlordService(
                $app->make(ILandlordRepository::class),
            );
        });


        $this->app->singleton(ICloudflareService::class, function ($app) {
            return new CloudflareService;
        });

        $this->app->singleton(IAgentPropertyService::class, function ($app) {
            return new AgentPropertyService(
                $app->make(IAgentRepository::class),
            );
        });

        $this->app->singleton(ILocalityService::class, function ($app) {
            return new LocalityService(
                $app->make(ILocalityRepository::class),
            );
        });

        $this->app->singleton(IProjectService::class, function ($app) {
            return new ProjectService(
                $app->make(IProjectRepository::class),
            );
        });

        $this->app->singleton(IProjectSyncService::class, function ($app) {
            return new ProjectSyncService(
                $app->make(IProjectRepository::class),
            );
        });


        $this->app->singleton(IAgentSyncService::class, function ($app) {
            return new AgilisAgentSyncService(
                $app->make(IAgentRepository::class),
            );
        });

        $this->app->singleton(IAgentSyncService::class, function ($app) {
            return new AgentSyncService(
                $app->make(IAgentRepository::class),
            );
        });

        $this->app->singleton(ILocalitySyncService::class, function ($app) {
            return new LocalitySyncService(
                $app->make(ILocalityRepository::class),
            );
        });

        $this->app->singleton(IPropertySyncService::class, function ($app) {
            return new PropertySyncService(
                $app->make(IPropertyRepository::class),
                $app->make(ICloudflareService::class),
            );
        });

        $this->app->singleton(IPropertySyncService::class, function ($app) {
            return new AgilisPropertySyncService(
                $app->make(IPropertyRepository::class),
                $app->make(ICloudflareService::class),
            );
        });

        $this->app->singleton(IAuthenticationService::class, function ($app) {
            return new AuthenticationService(
                $app->make(IUserService::class),
                $app->make(IUserTypeService::class),
                $app->make(IUniqueLinkService::class),
                $app->make(IEmailNotificationService::class),
                $app->make(IProfileService::class),
            );
        });

        $this->app->singleton(IUserService::class, function ($app) {
            return new UserService(
                $app->make(IUserRepository::class),
                $app->make(IUserTypeService::class),
                $app->make(IUniqueLinkService::class),
                $app->make(IEmailNotificationService::class),
                $app->make(IProfileService::class),
                $app->make(IPropertyService::class)
            );
        });

        $this->app->singleton(IUserTypeService::class, function ($app) {
            return new UserTypeService(
                $app->make(IUserTypeRepository::class)
            );
        });


        $this->app->singleton(IUniqueLinkService::class, function ($app) {
            return new UniqueLinkService(
                $app->make(IUniqueLinkRepository::class),
            );
        });

        $this->app->singleton(IEmailNotificationService::class, function ($app) {
            return new EmailNotificationService(
                $app->make(IEmailTemplateRepository::class),
                $app->make(INotificationTriggerRepository::class),
                $app->make(IEmailNotificationRepository::class)
            );
        });

        $this->app->singleton(IEmailService::class, function ($app) {
            return new EmailService;
        });

        $this->app->singleton(ICommandService::class, function ($app) {
            return new CommandService(
                $app->make(IEmailService::class),
                $app->make(IEmailNotificationService::class),
            );
        });

        $this->app->singleton(IProfileService::class, function ($app) {
            return new ProfileService(
                $app->make(IProfileRepository::class),
            );
        });

        $this->app->singleton(ISocialAuthenticationService::class, function ($app) {
            return new SocialAuthenticationService(
                $app->make(IUserService::class),
                $app->make(IProfileService::class),
            );
        });

        $this->app->singleton(IWishlistService::class, function ($app) {
            return new WishlistService(
                $app->make(IPropertyService::class),
                $app->make(IUserService::class),
                $app->make(IAPIv5Service::class)
            );
        });

        $this->app->singleton(IPropertyService::class, function ($app) {
            return new PropertyService(
                $app->make(IPropertyRepository::class),
                $app->make(IFormRepository::class),
                $app->make(IEmailNotificationService::class)
            );
        });

        $this->app->singleton(IAccountService::class, function ($app) {
            return new AccountService(
                $app->make(IUserService::class),
                $app->make(IProfileService::class),
            );
        });

        $this->app->singleton(IBuyerTypeService::class, function ($app) {
            return new BuyerTypeService(
                $app->make(IBuyerTypeRepository::class),
            );
        });

        $this->app->singleton(IInterestService::class, function ($app) {
            return new InterestService(
                $app->make(IInterestRepository::class),
            );
        });

        $this->app->singleton(IFormService::class, function ($app) {
            return new CareerService(
                $app->make(IFormRepository::class),
            );
        });

        $this->app->singleton(IFormService::class, function ($app) {
            return new ContactUsService(
                $app->make(IFormRepository::class),
            );
        });

        $this->app->singleton(ITypeService::class, function ($app) {
            return new TypeService(
                $app->make(ITypeRepository::class),
            );
        });

        $this->app->singleton(IEmailFrequencyService::class, function ($app) {
            return new EmailFrequencyService(
                $app->make(IEmailFrequencyRepository::class),
            );
        });

        $this->app->singleton(ISavedSearchService::class, function ($app) {
            return new SavedSearchService(
                $app->make(ISavedSearchRepository::class),
                $app->make(ITypeService::class),
                $app->make(IEmailFrequencyService::class),
            );
        });

        $this->app->singleton(IPropertyAlertService::class, function ($app) {
            return new PropertyAlertService(
                $app->make(IPropertyAlertRepository::class),
                $app->make(ITypeService::class),
            );
        });

        $this->app->singleton(IFormService::class, function ($app) {
            return new  AgentFormService(
                $app->make(IFormRepository::class),
            );
        });

        $this->app->singleton(IAgentService::class, function ($app) {
            return new  AgentService(
                $app->make(IAgentRepository::class),
            );
        });

        $this->app->singleton(IBranchService::class, function ($app) {
            return new  BranchService(
                $app->make(IBranchRepository::class),
            );
        });

        $this->app->singleton(ISavedPropertyService::class, function ($app) {
            return new SavedPropertyService(
                $app->make(IEmailNotificationService::class),
                $app->make(IWishlistService::class),
                $app->make(IPropertyRepository::class),
            );
        });

        $this->app->singleton(IImageService::class, function ($app) {
            return new ImageService;
        });

        $this->app->singleton(IVideoService::class, function ($app) {
            return new VideoService;
        });

        $this->app->singleton(IApiService::class, function ($app) {
            return new ApiService;
        });

        $this->app->singleton(IApiService::class, function ($app) {
            return new AgilisApiService(
                $app->make(ILandlordService::class)
            );
        });

        $this->app->singleton(IBranchSyncService::class, function ($app) {
            return new BranchSyncService(
                $app->make(IBranchService::class),
                $app->make(IAgentService::class)
            );
        });

        // VALIDATORS

        $this->app->singleton(IAuthenticationValidator::class, function ($app) {
            return new AuthenticationValidator;
        });

        $this->app->singleton(IUserValidator::class, function ($app) {
            return new UserValidator;
        });

        $this->app->singleton(ISocialAuthenticationValidator::class, function ($app) {
            return new SocialAuthenticationValidator;
        });

        $this->app->singleton(IWishlistValidator::class, function ($app) {
            return new WishlistValidator;
        });

        $this->app->singleton(IWishlistValidator::class, function ($app) {
            return new WishlistValidator;
        });

        $this->app->singleton(IAccountValidator::class, function ($app) {
            return new AccountValidator;
        });

        $this->app->singleton(ISavedSearchValidator::class, function ($app) {
            return new SavedSearchValidator;
        });

        $this->app->singleton(IPropertyAlertValidator::class, function ($app) {
            return new PropertyAlertValidator;
        });

        $this->app->singleton(ISavedPropertyValidator::class, function ($app) {
            return new SavedPropertyValidator;
        });



        //Custom Validator
        // Validator::extend('recaptcha', function ($attribute, $value, $parameters) {
        //     $client = new Client;
        //     $response = $client->post(
        //         'https://www.google.com/recaptcha/api/siteverify',
        //         [
        //             'form_params' =>
        //             [
        //                 'secret' => config('services.recaptcha.secret'),
        //                 'response' => $value
        //             ]
        //         ]
        //     );
        //     $body = json_decode((string)$response->getBody());
        //     return $body->success;
        // });

        // Validator::extend("emails", function ($attribute, $value, $parameters) {
        //     $rules = [
        //         'email' => 'required|email',
        //     ];

        //     $emails = explode(",", $value);

        //     foreach ($emails as $email) {
        //         $data = [
        //             'email' => trim($email)
        //         ];
        //         $validator = Validator::make($data, $rules);
        //         if ($validator->fails()) {
        //             return false;
        //         }
        //     }
        //     return true;
        // });

        // $this->app->singleton(\Illuminate\Contracts\Routing\ResponseFactory::class, function () {
        //     return new \Laravel\Lumen\Http\ResponseFactory();
        // });
    }
}
