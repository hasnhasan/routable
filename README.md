# Routable

##Install
    composer require hasnhasan/routable

##Usage
    class Blog extends Model
    {
        use Routable;
    
        public $routeName = 'blogController@detail'; //required
        public $slugColumn = 'title'; 
        public $slugPrefix = 'blog';
        
        ....
    }
    

    $blog = Blog::create([
        'title' => 'New Blog Item',
        '_route' => [ // Optional
            'title'       => 'Seo Title',
            'description' => 'seo Description',
            'keywords'    => 'seo keywords',
        ],
    ]);
    
    dd($blog->route);
    
    $blog = Blog::whereSlug('slug-key')->first();
    dd($blog);