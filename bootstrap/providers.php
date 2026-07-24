<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\OperationsPanelProvider;
use App\Providers\VoltServiceProvider;

return [
    AppServiceProvider::class,
    OperationsPanelProvider::class,
    VoltServiceProvider::class,
];
