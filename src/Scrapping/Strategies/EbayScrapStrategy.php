<?php

namespace App\Scrapping\Strategies;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class EbayScrapStrategy implements ScrapStrategyInterface
{
    const XPATH_PRODUCT_NAME = '//h1[@class="x-item-title__mainTitle"]/span';
    const XPATH_PRODUCT_IMAGE = '//div[@class="ux-image-carousel"]/div[1]/img/@src';
    const XPATH_PRODUCT_PRICE = '//span[@itemprop="price"]/span';

    public function getProductData($link)
    {
        $client = HttpClient::create();

        $crawler = new Crawler();
        $crawler->addHtmlContent($client->request('GET', $link)->getContent());

        return [
            'name' => $crawler->filterXPath(EbayScrapStrategy::XPATH_PRODUCT_NAME)->text(''),
            'image' => $crawler->filterXPath(EbayScrapStrategy::XPATH_PRODUCT_IMAGE)->text(''),
            'price' => $crawler->filterXPath(EbayScrapStrategy::XPATH_PRODUCT_PRICE)->text(''),
        ];
    }
}
