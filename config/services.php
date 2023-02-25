<?php

declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // UserRepository::class => \DI\autowire(InMemoryUserRepository::class),
    ]);
};

