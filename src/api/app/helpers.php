<?php

if (! function_exists('response_data')) {
    /**
     * Return a new response from the application.
     *
     * @param  mixed $data
     * @return App\Http\ApiResponse
     */
    function response_data($data)
    {
        return new App\Http\ApiResponse(0, $data);
    }
}

if (! function_exists('response_code')) {
    /**
     * Return a new response from the application.
     *
     * @param  integer $code
     * @return App\Http\ApiResponse
     */
    function response_code($code = 0, $data = null)
    {
        return new App\Http\ApiResponse($code, $data);
    }
}

if (! function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    function asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}
