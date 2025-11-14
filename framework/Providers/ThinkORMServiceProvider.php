<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: ThinkORMServiceProvider.php
 * @Date: 2025-11-14
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */
 
namespace Framework\Providers;

use Framework\Container\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use think\facade\Db;

class ThinkORMServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerConfigurator $config): void
    {
        $dbConfig = require BASE_PATH . '/config/database.php';

        $config->parameters()->set('db.config', $dbConfig);

        $config->services()
            ->set(Db::class, Db::class)
            ->public();
    }

    public function boot(ContainerInterface $container): void
    {
        $dbConfig = $container->getParameter('db.config');

        /** @var \Framework\Config\Config $appConfig */
        $appConfig = $container->get('config');

        /** @var \Framework\Logger\Logger $logger */
        $logger = $container->get('logger');

        // 初始化 ORM
        Db::setConfig($dbConfig);

        if ($appConfig->get('app.debug')) {
            Db::listen(static function ($sql, $time, $explain) use ($logger) {
                $logger->info('SQL Execution', [
                    'sql'     => $sql,
                    'time'    => $time . 's',
                    'explain' => $explain,
                ]);
            });
        }
    }
}
