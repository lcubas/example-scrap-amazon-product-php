<?php

declare(strict_types=1);

use Slim\App;
// use App\Middlewares\SessionMiddleware;

return function (App $app) {
    // Add routing middleware
    $app->addRoutingMiddleware();

    // Add body parsing middleware
    $app->addBodyParsingMiddleware();

    // $app->add(SessionMiddleware::class);
};
