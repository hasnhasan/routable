<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

$routableModel = config('routable.routableModel');

Route::bind('route', function ($value, $route) use ($routableModel) {
    $modelFolder = config('routable.modelFolder', 'Models');

    $model = ucfirst($route->bindingFieldFor('route'));
    $model = collect(File::allFiles(app_path($modelFolder)))->map(function ($class) {
        $classNamespace = str_replace([app_path(), '/', '.php'], ['App', '\\', ''], $class->getRealPath());
        $className      = class_basename($classNamespace);

        return (object) [
            'model'      => $classNamespace,
            'model_name' => $className,
        ];
    })->where('model_name', $model)->first();

    $route = $routableModel::where('slug', $value)->when($model, function ($q, $model) {
        return $q->where('routable_type', $model->model);
    })->firstOrFail();

    return $route->routable;
});

$parse = explode('/', Request::path());

if ($routeData = $routableModel::where('slug', end($parse))->first()) {
    $middleware = (array) config('routable.middleware');
    $action     = $routeData->uses;
    if (!strstr($action, '/')) {
        $action = 'App\Http\Controllers\\'.$action;
    }
    $prefix = (new $routeData->routable_type)->slugPrefix;
    Route::middleware($middleware)->get("{$prefix}/{$routeData->slug}", $action)->setDefaults([
        'routable' => $routeData->routable,
        'route'    => $routeData,
    ]);

}
