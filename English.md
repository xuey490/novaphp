## Introduction:
This is a lightweight, powerful, fast, simple, and secure PHP framework developed based on Symfony's underlying code.

## Download and Installation:
- **Local Environment Requirements:** PHP 8.1 or higher, Redis, MySQL 5.7 or higher
- Download the `main` branch from GitHub, extract it to a local directory, then run `php -S localhost:8000 -t public` in the root directory.
- Open your browser and visit http://localhost:8000

## Easter Eggs:
Open your browser and navigate to:
- http://localhost:8000/version
- http://localhost:8000/team

## Test Routes:
All controllers are located in `App/Controllers`. Access them via the pattern `http://localhost:8000/ControllerName/ActionName`, for example:

http://localhost:8000/user/add

## Version Milestones:
- **0.0.1** Basic framework setup, core routing dispatch completed

- **0.0.2** Implemented logging, DI (Dependency Injection) registration, improved routing

- **0.0.3** Implemented middleware and service registration

- **0.0.4** Implemented annotation-based routing; application layer now supports multiple middlewares

- **0.0.5** ORM completed; integrated ThinkORM, models fully compatible with ThinkPHP features

- **0.0.6** Completed:
    - Container management: Rewrote Container to use Symfony's container, PSR-11 compatible
    - Container supports direct service registration, as well as business operation classes; added helper functions
    - Configuration management completed, added optional configuration service registration
    - Logging service registration completed

- **0.0.7** Completed:
    - Rewrote logging service with support for log categorization, size limits, and archiving
    - Implemented exception handling service with user-friendly error display and request-ID for stack trace tracking
    - Modified core file `Framework.php` to add compatibility with Symfony Request and PSR-7
    - Added Kernel core file and `app()` helper function to retrieve service container or resolve services

- **0.0.8** Completed:
    - Integrated ThinkCache library to implement caching service
    - Integrated PHPUnit; run tests via `php phpunit.php` or `vendor/bin/phpunit`
    - Added version easter egg (purely for fun): access via http://localhost:8000/version
    - Added i18n (internationalization) support with automatic language pack loading; switch languages via URL: http://localhost:8000/?lang=en/zh_CN/zh_TW/ja

- **0.0.9** Completed:
    - Rewrote middleware system, added global variables and features: CORS, RateLimiter, CircuitBreaker, XSS filtering, IP Block
    - Implemented session service with support for Redis/file drivers

- **0.0.10**
    - Integrated Twig template engine, completed service registration, extensions, demos, and template routes: http://localhost:8000/blog/, http://localhost:8000/view
    - Rewrote CircuitBreaker and CSRF middleware; added Referer-based origin detection middleware
    - Reimplemented caching component using Symfony/Cache; PSR-16 compatibility dropped; ThinkCache will be deprecated in the next version (currently still usable via `app('cache')->set/get`)
    - Optimized routing: non-existent routes now automatically redirect to 404 page
    - Improved error display pages
    - Modified core file `Framework.php` to remove PSR-7 compatibility
    - Modified logging class to remove PSR-7 compatibility

> **0.0.10 is a milestone release**, featuring nearly all modern PHP framework capabilities.

**Enjoy using it!**
