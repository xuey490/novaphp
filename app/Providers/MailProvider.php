<?php
declare(strict_types=1);

namespace App\Providers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Framework\Container\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use App\Services\MailerService;

final class MailProvider implements ServiceProviderInterface
{
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 注册 MailerService
        $services
            ->set(MailerService::class, MailerService::class)
            ->autowire()
            ->autoconfigure()
            ->public();

        // 注册别名
        $services->alias('mailer', MailerService::class)->public();
    }

	public function boot(ContainerInterface $container): void
    #public function boot(ContainerConfigurator $configurator): void
    {
        //echo "[MailProvider] Booted. Mailer ready.\n";
    }
}
