<?php

declare(strict_types=1);

namespace Config;

use App\Libraries\ApiClient;
use App\Libraries\ApiClientInterface;
use App\Libraries\BffApiClient;
use App\Libraries\BffApiClientInterface;
use App\Libraries\DomainApiClient;
use App\Libraries\DomainApiClientInterface;
use App\Libraries\PermissionsSessionRefresher;
use App\Libraries\WebApiClient;
use App\Libraries\WebApiClientInterface;
use App\Modules\ApiKeys\Services\ApiKeyApiService;
use App\Modules\Audit\Services\AuditApiService;
use App\Modules\Auth\Services\AuthApiService;
use App\Modules\Cms\Services\BlockCatalogService;
use App\Modules\Cms\Services\BlockCatalogServiceInterface;
use App\Modules\Cms\Services\BlockInstanceApiService;
use App\Modules\Cms\Services\BlockTypeApiService;
use App\Modules\Cms\Services\BlockTypeOptionsResolver;
use App\Modules\Cms\Services\CategoryApiService;
use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\FileTranslationApiService;
use App\Modules\Cms\Services\LanguageApiService;
use App\Modules\Cms\Services\MenuApiService;
use App\Modules\Cms\Services\PageApiService;
use App\Modules\Cms\Services\RedirectApiService;
use App\Modules\Cms\Services\SettingApiService;
use App\Modules\Cms\Services\TagApiService;
use App\Modules\Cms\Services\TranslationAuditApiService;
use App\Modules\Dashboard\Services\HealthApiService;
use App\Modules\Files\Services\FileApiService;
use App\Modules\Iam\Services\ApplicationApiService;
use App\Modules\Iam\Services\PermissionApiService;
use App\Modules\Iam\Services\RoleApiService;
use App\Modules\Iam\Services\RoleMatrixApiService;
use App\Modules\Metrics\Services\MetricsApiService;
use App\Modules\Profile\Services\ProfileApiService;
use App\Modules\Users\Services\UserApiService;
use App\Support\Requests\FormRequestInterface;
use CodeIgniter\Config\BaseService;
use InvalidArgumentException;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function formRequest(string $class, bool $getShared = true): FormRequestInterface
    {
        if ($getShared) {
            /** @var FormRequestInterface */
            return static::getSharedInstance('formRequest', $class);
        }

        if (! class_exists($class)) {
            throw new InvalidArgumentException('Form request class does not exist: ' . $class);
        }

        $request = new $class(service('request'), service('validation'));

        if (! $request instanceof FormRequestInterface) {
            throw new InvalidArgumentException('Form request must implement FormRequestInterface: ' . $class);
        }

        return $request;
    }

    public static function apiClient(bool $getShared = true): ApiClientInterface
    {
        if ($getShared) {
            /** @var ApiClientInterface */
            return static::getSharedInstance('apiClient');
        }

        return new ApiClient(config('ApiClient'));
    }

    public static function domainApiClient(bool $getShared = true): DomainApiClientInterface
    {
        if ($getShared) {
            /** @var DomainApiClientInterface */
            return static::getSharedInstance('domainApiClient');
        }

        return new DomainApiClient(config('DomainApiClient'));
    }

    public static function bffApiClient(bool $getShared = true): BffApiClientInterface
    {
        if ($getShared) {
            /** @var BffApiClientInterface */
            return static::getSharedInstance('bffApiClient');
        }

        return new BffApiClient(config('BffApiClient'));
    }

    public static function authApiService(bool $getShared = true): AuthApiService
    {
        if ($getShared) {
            /** @var AuthApiService */
            return static::getSharedInstance('authApiService');
        }

        return new AuthApiService(static::apiClient());
    }

    public static function permissionsSessionRefresher(bool $getShared = true): PermissionsSessionRefresher
    {
        if ($getShared) {
            return static::getSharedInstance('permissionsSessionRefresher');
        }

        return new PermissionsSessionRefresher(static::authApiService());
    }

    public static function fileApiService(bool $getShared = true): FileApiService
    {
        if ($getShared) {
            /** @var FileApiService */
            return static::getSharedInstance('fileApiService');
        }

        return new FileApiService(static::apiClient(), static::domainApiClient());
    }

    public static function userApiService(bool $getShared = true): UserApiService
    {
        if ($getShared) {
            /** @var UserApiService */
            return static::getSharedInstance('userApiService');
        }

        return new UserApiService(static::apiClient());
    }

    public static function auditApiService(bool $getShared = true): AuditApiService
    {
        if ($getShared) {
            /** @var AuditApiService */
            return static::getSharedInstance('auditApiService');
        }

        return new AuditApiService(static::apiClient());
    }

    public static function apiKeyApiService(bool $getShared = true): ApiKeyApiService
    {
        if ($getShared) {
            /** @var ApiKeyApiService */
            return static::getSharedInstance('apiKeyApiService');
        }

        return new ApiKeyApiService(static::apiClient());
    }

    public static function metricsApiService(bool $getShared = true): MetricsApiService
    {
        if ($getShared) {
            /** @var MetricsApiService */
            return static::getSharedInstance('metricsApiService');
        }

        return new MetricsApiService(static::apiClient());
    }

    public static function healthApiService(bool $getShared = true): HealthApiService
    {
        if ($getShared) {
            /** @var HealthApiService */
            return static::getSharedInstance('healthApiService');
        }

        return new HealthApiService(static::apiClient(), config('ApiClient')->healthPaths);
    }

    public static function domainHealthApiService(bool $getShared = true): HealthApiService
    {
        if ($getShared) {
            /** @var HealthApiService */
            return static::getSharedInstance('domainHealthApiService');
        }

        return new HealthApiService(static::domainApiClient(), config('DomainApiClient')->healthPaths);
    }

    public static function bffHealthApiService(bool $getShared = true): HealthApiService
    {
        if ($getShared) {
            /** @var HealthApiService */
            return static::getSharedInstance('bffHealthApiService');
        }

        return new HealthApiService(static::bffApiClient(), config('BffApiClient')->healthPaths);
    }

    public static function webApiClient(bool $getShared = true): WebApiClientInterface
    {
        if ($getShared) {
            /** @var WebApiClientInterface */
            return static::getSharedInstance('webApiClient');
        }

        return new WebApiClient(config('WebApiClient'));
    }

    public static function webHealthApiService(bool $getShared = true): HealthApiService
    {
        if ($getShared) {
            /** @var HealthApiService */
            return static::getSharedInstance('webHealthApiService');
        }

        return new HealthApiService(static::webApiClient(), config('WebApiClient')->healthPaths);
    }

    public static function profileApiService(bool $getShared = true): ProfileApiService
    {
        if ($getShared) {
            /** @var ProfileApiService */
            return static::getSharedInstance('profileApiService');
        }

        return new ProfileApiService(static::apiClient());
    }

    public static function roleApiService(bool $getShared = true): RoleApiService
    {
        if ($getShared) {
            /** @var RoleApiService */
            return static::getSharedInstance('roleApiService');
        }

        return new RoleApiService(static::apiClient());
    }
    public static function roleMatrixApiService(bool $getShared = true): RoleMatrixApiService
    {
        if ($getShared) {
            /** @var RoleMatrixApiService */
            return static::getSharedInstance('roleMatrixApiService');
        }

        return new RoleMatrixApiService(static::apiClient());
    }
    public static function permissionApiService(bool $getShared = true): PermissionApiService
    {
        if ($getShared) {
            /** @var PermissionApiService */
            return static::getSharedInstance('permissionApiService');
        }

        return new PermissionApiService(static::apiClient());
    }
    public static function applicationApiService(bool $getShared = true): ApplicationApiService
    {
        if ($getShared) {
            /** @var ApplicationApiService */
            return static::getSharedInstance('applicationApiService');
        }

        return new ApplicationApiService(static::apiClient());
    }
    public static function languageApiService(bool $getShared = true): LanguageApiService
    {
        if ($getShared) {
            /** @var LanguageApiService */
            return static::getSharedInstance('languageApiService');
        }
        return new LanguageApiService(static::domainApiClient());
    }
    public static function settingApiService(bool $getShared = true): SettingApiService
    {
        if ($getShared) {
            /** @var SettingApiService */
            return static::getSharedInstance('settingApiService');
        }
        return new SettingApiService(static::domainApiClient());
    }
    public static function pageApiService(bool $getShared = true): PageApiService
    {
        if ($getShared) {
            /** @var PageApiService */
            return static::getSharedInstance('pageApiService');
        }
        return new PageApiService(static::domainApiClient());
    }
    public static function menuApiService(bool $getShared = true): MenuApiService
    {
        if ($getShared) {
            /** @var MenuApiService */
            return static::getSharedInstance('menuApiService');
        }
        return new MenuApiService(static::domainApiClient());
    }
    public static function blockInstanceApiService(bool $getShared = true): BlockInstanceApiService
    {
        if ($getShared) {
            /** @var BlockInstanceApiService */
            return static::getSharedInstance('blockInstanceApiService');
        }
        return new BlockInstanceApiService(static::domainApiClient());
    }
    public static function blockTypeApiService(bool $getShared = true): BlockTypeApiService
    {
        if ($getShared) {
            /** @var BlockTypeApiService */
            return static::getSharedInstance('blockTypeApiService');
        }
        return new BlockTypeApiService(static::domainApiClient());
    }

    public static function blockCatalogService(bool $getShared = true): BlockCatalogServiceInterface
    {
        if ($getShared) {
            /** @var BlockCatalogService */
            return static::getSharedInstance('blockCatalogService');
        }
        return new BlockCatalogService(static::blockTypeApiService());
    }

    public static function blockTypeOptionsResolver(bool $getShared = true): BlockTypeOptionsResolver
    {
        if ($getShared) {
            /** @var BlockTypeOptionsResolver */
            return static::getSharedInstance('blockTypeOptionsResolver');
        }
        return new BlockTypeOptionsResolver(
            static::blockCatalogService(),
            static::formApiService(),
            static::collectionApiService(),
            static::pageApiService(),
            static::entryApiService(),
        );
    }

    public static function collectionApiService(bool $getShared = true): CollectionApiService
    {
        if ($getShared) {
            /** @var CollectionApiService */
            return static::getSharedInstance('collectionApiService');
        }
        return new CollectionApiService(static::domainApiClient());
    }
    public static function entryApiService(bool $getShared = true): EntryApiService
    {
        if ($getShared) {
            /** @var EntryApiService */
            return static::getSharedInstance('entryApiService');
        }
        return new EntryApiService(static::domainApiClient());
    }
    public static function categoryApiService(bool $getShared = true): CategoryApiService
    {
        if ($getShared) {
            /** @var CategoryApiService */
            return static::getSharedInstance('categoryApiService');
        }
        return new CategoryApiService(static::domainApiClient());
    }
    public static function tagApiService(bool $getShared = true): TagApiService
    {
        if ($getShared) {
            /** @var TagApiService */
            return static::getSharedInstance('tagApiService');
        }
        return new TagApiService(static::domainApiClient());
    }
    public static function redirectApiService(bool $getShared = true): RedirectApiService
    {
        if ($getShared) {
            /** @var RedirectApiService */
            return static::getSharedInstance('redirectApiService');
        }
        return new RedirectApiService(static::domainApiClient());
    }

    public static function translationAuditApiService(bool $getShared = true): TranslationAuditApiService
    {
        if ($getShared) {
            /** @var TranslationAuditApiService */
            return static::getSharedInstance('translationAuditApiService');
        }
        return new TranslationAuditApiService(static::domainApiClient());
    }

    public static function fileTranslationApiService(bool $getShared = true): FileTranslationApiService
    {
        if ($getShared) {
            /** @var FileTranslationApiService */
            return static::getSharedInstance('fileTranslationApiService');
        }
        return new FileTranslationApiService(static::domainApiClient());
    }

    public static function formSubmissionApiService(bool $getShared = true): \App\Modules\Cms\Services\FormSubmissionApiService
    {
        if ($getShared) {
            return static::getSharedInstance('formSubmissionApiService');
        }
        return new \App\Modules\Cms\Services\FormSubmissionApiService(static::domainApiClient());
    }

    public static function analyticsApiService(bool $getShared = true): \App\Modules\Analytics\Services\AnalyticsApiService
    {
        if ($getShared) {
            return static::getSharedInstance('analyticsApiService');
        }
        return new \App\Modules\Analytics\Services\AnalyticsApiService(static::domainApiClient());
    }

    public static function formApiService(bool $getShared = true): \App\Modules\Cms\Services\FormApiService
    {
        if ($getShared) {
            return static::getSharedInstance('formApiService');
        }
        return new \App\Modules\Cms\Services\FormApiService(static::domainApiClient());
    }
}
