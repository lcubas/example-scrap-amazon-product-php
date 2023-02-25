<?php

namespace App\Scrapping\Strategies;

interface ScrapStrategyInterface
{
    public function getProductData(string $link);
}
