<?php

namespace HasnHasan\Routable\Traits;

use HasnHasan\Routable\Models\DynamicRoute;
use Illuminate\Support\Facades\Route;

trait Routable
{

    /**
     * @var array
     */
    private static $routeData = [];

    protected static function bootRoutable()
    {

        self::saving(function ($item) {
            if (isset($item->_route)) {
                self::$routeData = $item->_route;
                unset($item->route);
            }

            return $item;
        });

        self::saved(function ($item) {
            self::routeSave($item);
            self::$routeData = [];
            self::clearBootedModels();

            return $item;
        });

        self::deleting(function ($item) {
            $item->route->delete();
            self::clearBootedModels();
        });
    }

    /**
     * @return mixed
     */
    public function route()
    {
        return $this->hasOne(DynamicRoute::class, 'parameter', 'id')
            ->where('uses', self::routeNameToUses());
    }

    /**
     * @param $query
     * @param  string  $slug
     *
     * @return mixed
     */
    public function scopeWhereSlug($query, $slug = '')
    {
        return $query->whereHas("route", function ($q) use ($slug) {
            $q->where('routes.slug', '=', $slug);
        });
    }

    /**
     * Route Save
     *
     * @param $item
     *
     * @return bool
     */
    public static function routeSave($item)
    {
        $colum = isset($item->slugColumn) ? $item->slugColumn : 'title';
        $slug  = $item->$colum;
        if (isset(self::$routeData['slug'])) {
            $slug = self::$routeData['slug'];
        }
        $slug = self::createSlug(str_slug($slug), $item);

        $route = $item->route ? $item->route : new DynamicRoute();

        $route->slug      = $slug;
        $route->uses      = (new self)->routeNameToUses();
        $route->parameter = $item->id;
        if (self::$routeData) {
            $route->fill(self::$routeData);
        }
        $route->save();

        return true;
    }

    /**
     * Create Slug
     *
     * @param $tmpSlug
     * @param $item
     * @param  int  $add
     *
     * @return string
     */
    public static function createSlug($tmpSlug, $item, $add = 0)
    {
        $idField    = $item->getKeyName();
        $id         = $item->$idField;
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
            return self::createSlug($tmpSlug, $item, $add + 1);
        }

        return $searchSlug;

    }

    /**
     * @return |null
     */
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
