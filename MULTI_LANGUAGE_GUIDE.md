# Multi-Language Implementation Guide

This guide explains how to implement and use the multi-language (i18n) system in this Laravel application. The system currently supports **English** and **Bahasa Indonesia**.

## 📁 File Structure

```
lang/
├── en/                          # English translations
│   └── messages.php
├── id/                          # Indonesian translations
│   └── messages.php
└── vendor/
    └── adminlte/               # AdminLTE package translations (already included)
        ├── en/
        └── id/

app/Http/
├── Controllers/
│   └── LanguageController.php  # Handles language switching
└── Middleware/
    └── SetLocale.php           # Applies selected language to each request

resources/views/
├── components/
│   └── language-switcher.blade.php    # Reusable language switcher component
└── vendor/adminlte/partials/navbar/
    └── menu-item-language-switcher.blade.php  # Language switcher for navbar
```

## 🚀 How It Works

### 1. **Middleware (SetLocale)**
The `SetLocale` middleware automatically:
- Checks if a language is stored in the session
- Applies the stored language to every request
- Falls back to browser language detection if no language is selected
- Defaults to English if no preference is found

### 2. **Language Storage**
The selected language is stored in the user's session and persists across page loads.

### 3. **Language Switching**
Users can switch languages using the language dropdown in the top navbar. The selection is saved and applied immediately.

## 📝 Usage in Views

### Method 1: Using Translation Helpers

#### Simple Translation
```php
{{ __('messages.dashboard') }}
// Output: "Dashboard" (English) or "Dasbor" (Indonesian)
```

#### Translation with Parameters
```php
{{ __('messages.best_times_session', ['session' => 5]) }}
// Output: "Best Times - Session 5" or "Waktu Terbaik - Sesi 5"
```

#### Translation in Attributes
```blade
<button type="button" aria-label="{{ __('messages.close') }}">
    {{ __('messages.confirm') }}
</button>
```

### Method 2: Using @lang Directive
```blade
@lang('messages.welcome')
```

### Method 3: Using trans() Function
```php
{!! trans('messages.some_key') !!}
```

## 🎨 Adding the Language Switcher to Your Views

### In Any Blade View Using AdminLTE Layout

Add this to the top of your blade file (after `@extends`):

```blade
@extends('adminlte::page')

@section('content_top_nav_right')
    @include('vendor.adminlte.partials.navbar.menu-item-language-switcher')
@stop

@section('content')
    <!-- Your content here -->
@stop
```

### As a Standalone Component

You can also use the standalone component anywhere:

```blade
@include('components.language-switcher')
```

## ➕ Adding New Translations

### Step 1: Add to English Translation File (`lang/en/messages.php`)

```php
return [
    // ... existing translations ...
    
    'my_new_key' => 'My New Text',
    'greeting' => 'Hello, :name!',
    'items_count' => '{0} No items|{1} One item|[2,*] :count items',
];
```

### Step 2: Add to Indonesian Translation File (`lang/id/messages.php`)

```php
return [
    // ... existing translations ...
    
    'my_new_key' => 'Teks Baru Saya',
    'greeting' => 'Halo, :name!',
    'items_count' => '{0} Tidak ada item|{1} Satu item|[2,*] :count item',
];
```

### Step 3: Use in Your Views

```blade
{{ __('messages.my_new_key') }}
{{ __('messages.greeting', ['name' => 'John']) }}
@choice('messages.items_count', $count, ['count' => $count])
```

## 🌍 Adding a New Language

### Step 1: Create Language Directory

```bash
mkdir lang/es  # For Spanish, for example
```

### Step 2: Create Translation File

```bash
# Copy from English as a template
cp lang/en/messages.php lang/es/messages.php
```

### Step 3: Translate the Content

Edit `lang/es/messages.php` and translate all values to Spanish.

### Step 4: Update SetLocale Middleware

Add the new locale to the supported locales array in `app/Http/Middleware/SetLocale.php`:

```php
$supportedLocales = ['en', 'id', 'es'];  // Add 'es' here
```

### Step 5: Update Language Controller

Add the new locale to the supported locales array in `app/Http/Controllers/LanguageController.php`:

```php
$supportedLocales = ['en', 'id', 'es'];  // Add 'es' here
```

### Step 6: Update Language Switcher View

Add the new language option in `resources/views/vendor/adminlte/partials/navbar/menu-item-language-switcher.blade.php`:

```blade
<form action="{{ route('language.switch') }}" method="POST" id="locale-es-form" class="d-none">
    @csrf
    <input type="hidden" name="locale" value="es">
</form>

<a href="#" class="dropdown-item {{ app()->getLocale() == 'es' ? 'active' : '' }}"
   onclick="event.preventDefault(); document.getElementById('locale-es-form').submit();">
    <i class="fas fa-flag mr-2"></i> Español
</a>
```

## 🔧 Configuration

### Default Language

To change the default language, update `.env`:

```env
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```

Or directly in `config/app.php`:

```php
'locale' => env('APP_LOCALE', 'en'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
```

### Current Locale in Code

Get the current language in your PHP code:

```php
$currentLocale = app()->getLocale();  // Returns 'en' or 'id'
```

In Blade views:

```blade
@if(app()->getLocale() == 'id')
    <!-- Indonesian specific content -->
@else
    <!-- English specific content -->
@endif
```

## 📋 Best Practices

### 1. **Organize Translation Keys**

Use descriptive, hierarchical keys:

```php
// ❌ Bad
'btn1' => 'Save',
'btn2' => 'Cancel',

// ✅ Good
'button.save' => 'Save',
'button.cancel' => 'Cancel',
'user.profile.edit' => 'Edit Profile',
```

### 2. **Create Separate Translation Files for Different Sections**

Instead of one large `messages.php`, create specific files:

```
lang/en/
├── messages.php       # General messages
├── auth.php          # Authentication related
├── validation.php    # Validation messages
└── tournaments.php   # Tournament specific
```

Use them like:

```php
{{ __('auth.login') }}
{{ __('tournaments.create_new') }}
```

### 3. **Use Pluralization**

```php
// Translation file
'apples' => '{0} No apples|{1} One apple|[2,*] :count apples',

// View
@choice('messages.apples', $count, ['count' => $count])
```

### 4. **Keep HTML Out of Translations**

```php
// ❌ Bad
'welcome' => 'Welcome <strong>back</strong>!',

// ✅ Good
'welcome' => 'Welcome back!',

// In view:
<strong>{{ __('messages.welcome') }}</strong>
```

### 5. **Use Parameters for Dynamic Content**

```php
// Translation
'greeting' => 'Hello, :name! You have :count messages.',

// View
{{ __('messages.greeting', ['name' => $user->name, 'count' => $messageCount]) }}
```

## 🧪 Testing Translations

### Check if a Translation Key Exists

```php
if (Lang::has('messages.some_key')) {
    echo __('messages.some_key');
}
```

### Get Translation in Specific Language

```php
// Get Indonesian translation regardless of current locale
$text = trans('messages.dashboard', [], 'id');
```

## 📌 Example: Converting an Existing View

### Before (Hardcoded English)

```blade
<h1>Dashboard</h1>
<p>Welcome to the tournament management system</p>
<button>Save Changes</button>
```

### After (Multi-language)

```blade
<h1>{{ __('messages.dashboard') }}</h1>
<p>{{ __('messages.welcome_text') }}</p>
<button>{{ __('messages.save_changes') }}</button>
```

## 🎯 Quick Reference

| Function | Usage | Example |
|----------|-------|---------|
| `__()` | Translate text | `{{ __('messages.hello') }}` |
| `@lang()` | Translate in template | `@lang('messages.hello')` |
| `trans()` | Translate with HTML | `{!! trans('messages.hello') !!}` |
| `@choice()` | Pluralization | `@choice('messages.apples', 5)` |
| `app()->getLocale()` | Get current language | `if (app()->getLocale() == 'id')` |
| `app()->setLocale()` | Set language (temporary) | `app()->setLocale('id')` |

## 🔗 Routes

- **Switch Language**: `POST /language/switch` (with `locale` parameter: `en` or `id`)

## 🐛 Troubleshooting

### Translation Not Showing

1. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

2. **Check if the key exists in both language files**

3. **Verify the syntax:**
   ```php
   // ✅ Correct
   {{ __('messages.dashboard') }}
   
   // ❌ Wrong
   {{ __('dashboard') }}  // Missing file prefix
   ```

### Language Not Switching

1. **Check if sessions are working:**
   ```bash
   php artisan session:table  # If using database sessions
   php artisan migrate
   ```

2. **Verify middleware is registered** in `bootstrap/app.php`

3. **Clear browser cookies/session**

## 📚 Additional Resources

- [Laravel Localization Documentation](https://laravel.com/docs/localization)
- [AdminLTE Translation](https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Translations)

---

**Implementation Date:** December 2025  
**Supported Languages:** English (en), Bahasa Indonesia (id)

