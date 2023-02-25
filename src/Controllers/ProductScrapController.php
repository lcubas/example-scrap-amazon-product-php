<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Controller;
use App\Services\ProductScrapService;
use Psr\Http\Message\ResponseInterface as Response;

class ProductScrapController extends Controller
{
    protected ProductScrapService $productScrapService;

    public function __construct(ProductScrapService $productScrapService)
    {
        $this->productScrapService = $productScrapService;
    }

    /**
     * {@inheritdoc}
     */
    protected function handle(): Response
    {
        $linkProduct = (string) $this->getFormData('link');
        $productData = $this->productScrapService->scrap($linkProduct);

        return $this->respondWithData($productData);
    }
}
