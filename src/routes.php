<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

$routableModel = config('routable.routableModel');
if ($routeData = $routableModel::where('slug', Request::path())->first()) {
    $middleware = (array) config('routable.middleware');
    Route::middleware($middleware)->get($routeData->slug, $routeData->uses);
}