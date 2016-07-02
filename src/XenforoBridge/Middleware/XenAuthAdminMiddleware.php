<?php

namespace XenforoBridge\Middleware;

use Closure;
use Config;
use Redirect;
use Session;
use XenforoBridge\XenforoBridge;

class XenAuthAdminMiddleware
{
    /**
     * stores Xenforo Bridge class.
     *
     * @var XenforoBridge\XenforoBridge
     */
    private $xenforo;

    /**
     * Construct Middleware Class.
     *
     * @param \XenforoBridge\XenforoBridge $xenforo
     */
    public function __construct(XenforoBridge $xenforo)
    {
        $this->xenforo = $xenforo;
    }

    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $xenBaseUrl = config('xenforobridge.xenforo_base_url_path');

        if (!$this->xenforo->isAdmin() and !$this->xenforo->isBanned()) {
            Session::put('loginRedirect', $request->url());

            return Redirect::to($xenBaseUrl.'login');
        }

        return $next($request);
    }
}
