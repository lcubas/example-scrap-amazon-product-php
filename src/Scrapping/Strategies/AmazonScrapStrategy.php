<?php

namespace App\Scrapping\Strategies;

use App\Exceptions\ScrapException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AmazonScrapStrategy implements ScrapStrategyInterface
{
    private $client;

    private const DEFAULT_HEADERS = [
        'Accept' => '*/*',
        'Accept-Language' => 'es-ES,es;q=0.9',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0',
    ];

    private const LOCALIZATION_DATA = [
        "locationType" => "LOCATION_INPUT",
        "zipCode" => "33139",
        "storeContext" => "generic",
        "deviceType" => "web",
        "pageType" => "Gateway",
        "actionSource" => "glow"
    ];

    private const BASE_URL = 'https://www.amazon.com/dp/%s?language=es_US';
    private const CHANGE_LOCALIZATION_URL = 'https://www.amazon.com/portal-migration/hz/glow/address-change?actionSource=glow';
    private const GET_CSRF_TOKEN_URL = 'https://www.amazon.com/portal-migration/hz/glow/get-rendered-address-selections?deviceType=desktop&pageType=Gateway&storeContext=NoStoreName&actionSource=desktop-modal';

    private const XPATH_PRODUCT_NAME = '//span[@id="productTitle"]';
    private const XPATH_PRODUCT_IMAGE = '//div[@id="imgTagWrapperId"]/img/@src';
    private const XPATH_PRODUCT_PRICE = '//span[contains(@class, "priceToPay")]/span[1]';
    private const XPATH_GET_AJAX_TOKEN = '//span[@id="nav-global-location-data-modal-action"]/@data-a-modal';

    public function __construct()
    {
        $this->client = HttpClient::create([
            'headers' => self::DEFAULT_HEADERS
        ]);
    }

    public function getProductData(string $link)
    {
        $asin = $this->getASINFromLink($link);
        $productLink = sprintf(self::BASE_URL, $asin);
        $cookies = $this->getCookiesCustomLocalization();
        $response = $this->client->request('GET', $productLink, [
            'headers' => [
                'Cookie' => $cookies
            ]
        ]);

        $crawler = new Crawler();
        $crawler->addHtmlContent($response->getContent());

        return [
            'name' => $crawler->filterXPath(self::XPATH_PRODUCT_NAME)->text(''),
            'image' => $crawler->filterXPath(self::XPATH_PRODUCT_IMAGE)->text(''),
            'price' => $crawler->filterXPath(self::XPATH_PRODUCT_PRICE)->text(''),
            // 'overview_features' => $this->getOverviewFetures($crawler),
            // 'variations' => $this->getVariations($crawler),
        ];
    }

    private function getASINFromLink(string $link)
    {
        $regexp = '/(?:[\/dp\/]|$)([A-Z0-9]{10})/';

        preg_match($regexp, $link, $matches);

        return $matches[1];
    }

    private function getProductContentByASIN(string $asin)
    {
        return new Crawler();
    }

    private function getVariations(Crawler $content)
    {
        $xpathVariations = '//div[@id="twisterContainer"]//form/div[@class="a-section a-spacing-small"]';

        return $content->filterXPath($xpathVariations)->each(function($node) {
            $variationName = $node->filterXpath('//div[@class="a-row"]/label')->text('text');
            $variationSelectedValue = $node->filterXpath('//div[@class="a-row"]/span')->text('text');
            $products = $node->filterXpath('//ul/li')->each(function($subNode) {
                $asin = $subNode->attr('data-defaultasin');
                $content = $this->getProductContentByASIN($asin);

                if (!$content) return;

                return [
                    'name' => $content->filterXPath(self::XPATH_PRODUCT_NAME)->text(''),
                    'image' => $content->filterXPath(self::XPATH_PRODUCT_IMAGE)->text(''),
                    'price' => $content->filterXPath(self::XPATH_PRODUCT_PRICE)->text(''),
                    // 'overview_features' => $this->getOverviewFetures($content),
                ];
            });

            return [
                'name' => $variationName,
                'items' => $products,
            ];
        });
    }

    // private function getOverviewFetures(Crawler $content)
    // {
    //     try {
    //         return $content->filterXPath('//div[@id="productOverview_feature_div"]//table/tr')
    //             ->each(function($node) {
    //                 $key = $node->filterXPath('//td[@class="a-span3"]/span')->text();
    //                 $value = $node->filterXPath('//td[@class="a-span9"]/span')->text();

    //                 return [
    //                     'key' => $key,
    //                     'value' => $value,
    //                 ];
    //             });
    //     } catch (\Throwable $th) {
    //         return [];
    //     }
    // }

    private function getCookiesCustomLocalization(): string
    {
        $response = $this->client->request('GET', 'https://www.amazon.com');

        $crawler = new Crawler();
        $crawler->addHtmlContent($response->getContent());

        $ajaxToken = $this->getAjaxToken($crawler);
        $cookies = $this->getParseCookies($response);
        $csrfToken = $this->getCsrfToken($cookies, $ajaxToken);

        $response = $this->makeChangeLocalizationRequest($cookies, $csrfToken);

        return $this->getParseCookies($response);
    }

    private function makeChangeLocalizationRequest(string $cookies, string $csrfToken): ResponseInterface
    {
        return $this->client->request('POST', self::CHANGE_LOCALIZATION_URL, [
            'headers' => [
                'Cookie' => $cookies,
                'anti-csrftoken-a2z' => $csrfToken,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode(self::LOCALIZATION_DATA)
        ]);
    }

    private function getCsrfToken(string $cookies, string $ajaxToken): string
    {
        $response = $this->client->request('GET', self::GET_CSRF_TOKEN_URL, [
            'headers' => [
                'Cookie' => $cookies,
                'anti-csrftoken-a2z' => $ajaxToken,
            ]
        ]);

        preg_match('/CSRF_TOKEN : "(.+?)"/', $response->getContent(), $matches);

        return $matches[1];
    }

    private function getAjaxToken(Crawler $content)
    {
        $ajaxTokenData = $content->filterXPath(self::XPATH_GET_AJAX_TOKEN)->text('');
        $ajaxTokenData = json_decode($ajaxTokenData, true);

        if (empty($ajaxTokenData)) {
            throw new ScrapException('Value: \'ajax_csrf_token\' not found');
        }

        return $ajaxTokenData['ajaxHeaders']['anti-csrftoken-a2z'];
    }

    public function getParseCookies(ResponseInterface $response): string
    {
        $headers = $response->getHeaders()['set-cookie'];

        return implode(' ', $headers);
    }
}
