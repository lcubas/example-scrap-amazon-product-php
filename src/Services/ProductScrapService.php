<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ScrapException;
use App\Scrapping\ScrappingFactory;

class ProductScrapService
{
    public function scrap($link)
    {
        $hostLinkProduct = parse_url($link, PHP_URL_HOST);

        if (!isset($hostLinkProduct)) {
            throw new ScrapException('The [link] param is not valid');
        }

        $scrappingStrategy = ScrappingFactory::getScrapStrategy($hostLinkProduct);

        return $scrappingStrategy->getProductData($link);
    }
}
