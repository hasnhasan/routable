<?php

namespace HasnHasan\Routable\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicRoute extends Model
{

    public $table = 'routes';
    protected $casts = [
        'filters' => 'array',
    ];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function routable()
    {
        return $this->morphTo();
    }
}
