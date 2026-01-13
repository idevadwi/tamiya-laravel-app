<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch language
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request)
    {
        $locale = $request->input('locale');
        
        // Validate locale
        $supportedLocales = ['en', 'id'];
        
        if (!in_array($locale, $supportedLocales)) {
            return redirect()->back()->with('error', 'Unsupported language.');
        }
        
        // Store locale in session
        Session::put('locale', $locale);
        
        return redirect()->back()->with('success', 'Language changed successfully.');
    }
}

