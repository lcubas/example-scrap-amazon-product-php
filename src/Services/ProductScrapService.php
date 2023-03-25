<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ScrapException;
use App\Scrapping\ScrappingFactory;

class ProductScrapService
{
    /**
     * Scrap by providing link
     *
     * @param string $link
     * @return mixed
     */
    public function scrap(string $link)
    {
        $hostLinkProduct = parse_url($link, PHP_URL_HOST);

        if (!isset($hostLinkProduct)) {
            throw new ScrapException('The [link] param is not valid');
        }

        $regex = '/.+\/\/|www.|\..+/';
        $domain = preg_replace($regex, '', $hostLinkProduct);
        $scrappingStrategy = ScrappingFactory::getScrapStrategy($domain);

        return $scrappingStrategy->getProductData($link);
    }
}
