<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

$routableModel = config('routable.routableModel');
if ($routeData = $routableModel::where('slug', Request::path())->first()) {
    $middleware = (array) config('routable.middleware');
    $action     = $routeData->uses;
    if (!strstr($action, '/')) {
        $action = 'App\Http\Controllers\\'.$action;
    }

    Route::middleware($middleware)->get("{$routeData->slug}", $action)->setDefaults([
        'routable' => $routeData->routable,
        'route'    => $routeData,
    ]);

}