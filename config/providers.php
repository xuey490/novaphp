<?php
// config/providers.php
return [
    Framework\Providers\RequestProvider::class,
    Framework\Providers\ResponseProvider::class,
    Framework\Providers\SessionServiceProvider::class,
    Framework\Providers\CookieServiceProvider::class,
    Framework\Providers\MiddlewaresProvider::class,
    Framework\Providers\ConfigServiceProvider::class,
    Framework\Providers\LoggerServiceProvider::class,
    Framework\Providers\HandlerServiceProvider::class,
    Framework\Providers\CacheServiceProvider::class,
    Framework\Providers\ThinkORMServiceProvider::class,
    Framework\Providers\ValidateServiceProvider::class,
    Framework\Providers\TranslationServiceProvider::class,
	Framework\Providers\JwtServiceProvider::class,
	Framework\Providers\UploadServiceProvider::class,
    Framework\Providers\TwigServiceProvider::class,
    Framework\Providers\ThinkTempServiceProvider::class,

];
