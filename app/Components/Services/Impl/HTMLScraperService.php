<?php

namespace App\Components\Services\Impl;

use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use App\Components\Services\IHTMLScraperService;

class HTMLScraperService implements IHTMLScraperService
{
    public function scrape(string $htmlBody)
    {
        $crawler = new Crawler($htmlBody);

        $propertyRefNode = $crawler->filterXPath('//p[contains(text(), "Property Reference")]/strong');
        $property_ref = $propertyRefNode->count() > 0 ? $propertyRefNode->text() : 'Property ref not found';

        $consultantNameNode = $crawler->filterXPath('//p[contains(text(), "Consultant Name")]/strong');
        $consultant_name = $consultantNameNode->count() > 0 ? $consultantNameNode->text() : 'Consultant name not found';

        return [
            'property_ref' => $property_ref,
            'consultant_name' => $consultant_name,
        ];
    }
}
