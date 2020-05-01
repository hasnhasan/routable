<?php

namespace HasnHasan\Routable\Traits;

use HasnHasan\Routable\Models\DynamicRoute;
use Illuminate\Support\Facades\Route;

trait Routable
{

    protected $with = ['route'];

    protected static function bootRoutable()
    {
        self::saved(function ($item) {
            self::routeSave($item);
            self::clearBootedModels();
        });

        self::deleting(function ($item) {
            $item->route->delete();
            self::clearBootedModels();
        });
    }

    public function route()
    {
        return $this->hasOne(DynamicRoute::class, 'parameter', 'id')
            ->where('uses', $this->routeNameToUses());
    }

    public function scopeWhereSlug($query, $slug = '')
    {
        return $query->whereHas("route", function ($q) use ($slug) {
            $q->where('routes.slug', '=', $slug);
        });
    }

    public static function routeSave($item)
    {
        $colum = isset($item->slugColumn) ? $item->slugColumn : 'title';
        $slug  = self::createSlug(str_slug($item->$colum), $item);

        $route = $item->route ? $item->route : new DynamicRoute();

        $route->uses      = self::routeNameToUses();
        $route->parameter = $item->id;
        $route->slug      = $slug;

        if (isset($item->seo_title)) {
            $route->title = $item->seo_title;
        }

        if (isset($item->seo_desc)) {
            $route->description = $item->seo_desc;
        }

        if (isset($item->seo_keywords)) {
            $route->keywords = $item->seo_keywords;
        }

        try {
            $route->save();
        } catch (\Exception $e) {
            return false;
        }

        return $route;
    }

    public static function createSlug($tmpSlug, $item, $add = 0)
    {
        $id         = $item->$item->getKeyName();
        $searchSlug = $tmpSlug;

        #Prefix
        if (isset($item->slugPrefix) && $item->slugPrefix) {
            $searchSlug = $item->slugPrefix.'/'.$tmpSlug;
        }

        if ($add > 0) {
            $searchSlug .= '-'.$add;
        }

        $route = DynamicRoute::where('slug', $searchSlug)
            ->when($id, function ($q, $id) {
                return $q->where('parameter', '<>', $id);
            })
            ->exists();

        if ($route) {
            return self::createSlug($tmpSlug, $item, $add++);
        }

        return $searchSlug;

    }

    private function routeNameToUses()
    {
        $routes = collect(Route::getRoutes());
        $route  = $routes->where('action.as', $this->routeName)->first();
        if ($route) {
            return $route->action['uses'];
        }

        return null;
    }
}
