<?php

namespace App\Scrapping;

use App\Exceptions\ScrapException;
use App\Exceptions\ProductScrapException;
use App\Scrapping\Strategies\EbayScrapStrategy;
use App\Scrapping\Strategies\AmazonScrapStrategy;
use App\Scrapping\Strategies\ScrapStrategyInterface;

class ScrappingFactory
{
    /**
     * Get a scrap strategy by host.
     *
     * @param string $host
     *
     * @return ScrapStrategyInterface
     *
     * @throws ProductScrapException
     */
    public static function getScrapStrategy(string $host): ScrapStrategyInterface
    {
        $regex = '/.+\/\/|www.|\..+/';
        $domain = preg_replace($regex, '', $host);

        switch ($domain) {
            case "ebay":
                return new EbayScrapStrategy();
            case "amazon":
                return new AmazonScrapStrategy();
            default:
                throw new ScrapException("Unknown scrap strategy");
        }
    }
}
