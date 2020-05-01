<?php

namespace HasnHasan\Routable\Traits;

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
        return $this->hasOne(config('routable.routableModel'), 'parameter', 'id')
            ->where('uses', self::getRouteName());
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
        $colum = isset($item->slugColumn) ? $item->slugColumn : config('routable.defaultSlugColumn');
        $slug  = $item->$colum;
        if (isset(self::$routeData['slug'])) {
            $slug = self::$routeData['slug'];
        }

        $slug = self::createSlug(str_slug($slug, config('routable.separator'), config('routable.language')), $item);

        $routableModel = config('routable.routableModel');
        $route         = $item->route ? $item->route : new $routableModel();

        $route->slug      = $slug;
        $route->uses      = (new self)->getRouteName();
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
        $routableModel = config('routable.routableModel');
        $route         = $routableModel::where('slug', $searchSlug)
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
     * @return mixed
     */
    public function getRouteName()
    {
        return $this->routeName;
    }
}
