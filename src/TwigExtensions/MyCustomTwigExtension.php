<?php

namespace App\TwigExtensions;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class MyCustomTwigExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('defaultImage', [$this, 'defaultImage']),
        ];
    }

    public function defaultImage(string $path): string{
        if(strlen(trim($path)) == 0){
            return '2019-03-27.jpg';
        }
        return $path;
    }
}