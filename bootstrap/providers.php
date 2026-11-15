<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\MetadataServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    MetadataServiceProvider::class,
];
