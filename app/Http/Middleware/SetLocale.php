<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if locale is stored in session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } else {
            // Default to browser language if available
            $locale = $this->getBrowserLocale($request);
            Session::put('locale', $locale);
        }
        
        // Set the application locale
        App::setLocale($locale);
        
        return $next($request);
    }
    
    /**
     * Get browser locale
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    private function getBrowserLocale(Request $request)
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return config('app.locale', 'en');
        }
        
        // Parse the Accept-Language header
        $languages = explode(',', $acceptLanguage);
        $supportedLocales = ['en', 'id'];
        
        foreach ($languages as $language) {
            $locale = substr($language, 0, 2);
            if (in_array($locale, $supportedLocales)) {
                return $locale;
            }
        }
        
        return config('app.locale', 'en');
    }
}

