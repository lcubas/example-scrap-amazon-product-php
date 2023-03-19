<?php

declare(strict_types=1);

use Monolog\Logger;
use DI\ContainerBuilder;
use App\Settings\Settings;
use App\Settings\SettingsInterface;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => true,
                'logErrorDetails'     => true,
                'logger' => [
                    'name' => 'app',
                    'path' => __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
            ]);
        }
    ]);
};
