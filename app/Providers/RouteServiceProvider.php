<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Route::pattern('id', '[0-9]+');
        Route::pattern('admin_setting_type', implode('|', array_keys(config('applicationsettings.settings'))));
        Route::pattern('menu_slug', implode('|', config('navigation.registered_place')));
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapPermissionApiRoutes();
        $this->mapGuestPermissionApiRoutes();
        $this->mapVerificationPermissionApiRoutes();

        $this->mapWebRoutes();
        $this->mapPermissionRoutes();
        $this->mapGuestPermissionRoutes();
        $this->mapVerificationPermissionRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    protected function mapPermissionApiRoutes()
    {
        $filename = 'permission_api';
        $middleware = ['api', 'auth:api', 'permission.api'];
        $prefix = $namespace = null;
        $this->routeMap($filename, $middleware, $prefix, $namespace);
    }

    protected function routeMap($filename, $middleware, $prefix = null, $namespace = null, $path = 'routes/groups/')
    {
        $locale = strtolower($this->app->request->segment(1));
        $language = check_language($locale);
        if ($language != null && $prefix != null) {
            $prefix = $language . '/' . $prefix;
        } elseif ($language != null) {
            $prefix = $language;
        }

        if ($namespace != null) {
            $namespace = $this->namespace . '\\' . ucfirst($namespace);
        } else {
            $namespace = $this->namespace;
        }

        Route::prefix($prefix)
            ->middleware($middleware)
            ->namespace($namespace)
            ->group(base_path($path . $filename . '.php'));
    }

    protected function mapGuestPermissionApiRoutes()
    {
        $filename = 'guest_permission_api';
        $middleware = ['api', 'guest.permission.api'];
        $prefix = $namespace = null;
        $this->routeMap($filename, $middleware, $prefix, $namespace);
    }

    protected function mapVerificationPermissionApiRoutes()
    {
        $filename = 'verification_permission_api';
        $middleware = ['api', 'verification.permission.api'];
        $prefix = $namespace = null;
        $this->routeMap($filename, $middleware, $prefix, $namespace);
    }

    // API Starts here

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */

    protected function mapWebRoutes()
    {
        $filename = $middleware = 'web';
        $middleware = ['web', 'menuable'];
        $prefix = $namespace = null;
        $this->routeMap($filename, $middleware, $prefix, $namespace, 'routes/');
    }

    protected function mapPermissionRoutes()
    {
        $filename = 'permission';
        $middleware = ['web', 'auth', 'permission'];
        $prefix = $namespace = null;
        $this->routeMap($filename, $middleware, $prefix, $namespace);
    }

    protected function mapGuestPermissionRoutes()
    {
        $filename = 'guest_permission';
        $middleware = ['web', 'guest.permission'];
        $prefix = $namespace = null;
        $this->routeMap($filename, $middleware, $prefix, $namespace);
    }

    protected function mapVerificationPermissionRoutes()
    {
        $filename = 'verification_permission';
        $middleware = ['web', 'verification.permission'];
        $prefix = $namespace = null;
        $this->routeMap($filename, $middleware, $prefix, $namespace);
    }
}
