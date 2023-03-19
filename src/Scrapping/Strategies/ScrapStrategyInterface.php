<?php

namespace App\Scrapping\Strategies;

interface ScrapStrategyInterface
{
    /**
     * Get product information from a certain link
     *
     * @param string $link
     * @return array
     */
    public function getProductData(string $link);
}
