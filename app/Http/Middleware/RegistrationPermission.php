<?php

namespace App\Http\Middleware;

use Closure;

class RegistrationPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(settings('registration_active_status') != ACTIVE_STATUS_ACTIVE)
        {
            abort(404, __('Registration is currently disabled.'));
        }

        return $next($request);
    }
}
