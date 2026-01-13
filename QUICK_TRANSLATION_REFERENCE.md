# Quick Translation Reference Card

## 🎯 3-Step Process to Add Multi-Language to Any Page

### Step 1: Add Language Switcher
Add this right after `@extends('adminlte::page')`:

```blade
@section('content_top_nav_right')
    @include('vendor.adminlte.partials.navbar.menu-item-language-switcher')
@stop
```

### Step 2: Replace Hardcoded Text
Find hardcoded English text and replace with translation helpers:

```blade
{{-- Before --}}
<h1>Teams</h1>
<p>List of all teams</p>
<button>Create New</button>

{{-- After --}}
<h1>{{ __('messages.teams') }}</h1>
<p>{{ __('messages.list_of_all_teams') }}</p>
<button>{{ __('messages.create_new') }}</button>
```

### Step 3: Add Missing Keys to Translation Files
If you use a new key that doesn't exist yet:

```php
// lang/en/messages.php
'list_of_all_teams' => 'List of all teams',
'create_new' => 'Create New',

// lang/id/messages.php
'list_of_all_teams' => 'Daftar semua tim',
'create_new' => 'Buat Baru',
```

## 📋 Common Translation Patterns

### Buttons
```blade
{{ __('messages.save') }}
{{ __('messages.cancel') }}
{{ __('messages.edit') }}
{{ __('messages.delete') }}
{{ __('messages.create') }}
{{ __('messages.update') }}
{{ __('messages.back') }}
{{ __('messages.confirm') }}
```

### Form Labels
```blade
<label>{{ __('messages.team_name') }}</label>
<label>{{ __('messages.email') }}</label>
<label>{{ __('messages.password') }}</label>
```

### Status Badges
```blade
{{ __('messages.active') }}
{{ __('messages.inactive') }}
{{ __('messages.completed') }}
{{ __('messages.cancelled') }}
{{ __('messages.pending') }}
```

### Page Titles
```blade
@section('title', __('messages.teams'))
@section('title', __('messages.dashboard'))
```

### With Parameters
```blade
{{-- Translation: 'welcome_back' => 'Welcome back, :name!' --}}
{{ __('messages.welcome_back', ['name' => $user->name]) }}

{{-- Translation: 'session_number' => 'Session :number' --}}
{{ __('messages.session_number', ['number' => 5]) }}
```

### In HTML Attributes
```blade
<button aria-label="{{ __('messages.close') }}">X</button>
<input placeholder="{{ __('messages.search') }}">
<img alt="{{ __('messages.user_avatar') }}">
```

## 🔍 Already Available Translation Keys

These keys are already defined and ready to use:

### General
- `dashboard`, `home`, `settings`, `profile`
- `save`, `cancel`, `edit`, `delete`, `create`, `update`
- `search`, `filter`, `export`, `import`, `back`
- `yes`, `no`, `confirm`, `close`

### Tournament Related
- `tournament_dashboard`, `tournament_name`, `tournament_information`
- `tournament_details`, `tournament_settings`
- `active_tournament`, `switch_tournament`
- `current_stage`, `vendor_name`, `track_number`
- `max_racers_per_team`, `current_bto_session`

### Entities
- `teams`, `racers`, `cards`, `races`, `tournaments`, `users`
- `view_teams`, `view_racers`, `view_cards`, `view_races`
- `manage_teams`, `manage_racers`, `manage_cards`, `manage_best_times`

### Best Times
- `best_times_overall`, `best_times_session`
- `track`, `team`, `best_time`
- `no_time_recorded`, `record_one_now`, `view_all`

### Status
- `status`, `active`, `inactive`, `completed`, `cancelled`, `pending`

### Actions
- `quick_actions`, `actions`, `proceed_to_next_round`

### Messages
- `success`, `error`, `warning`, `info`

### Authentication
- `login`, `logout`, `register`
- `email`, `password`, `remember_me`, `forgot_password`

## 📝 Example: Converting a Page

### Original teams/index.blade.php
```blade
@extends('adminlte::page')

@section('title', 'Teams')

@section('content_header')
    <h1>Teams Management</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List of Teams</h3>
            <div class="card-tools">
                <a href="{{ route('teams.create') }}" class="btn btn-primary">
                    Create New Team
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop
```

### Converted with Translations
```blade
@extends('adminlte::page')

@section('title', __('messages.teams'))

@section('content_top_nav_right')
    @include('vendor.adminlte.partials.navbar.menu-item-language-switcher')
@stop

@section('content_header')
    <h1>{{ __('messages.manage_teams') }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('messages.teams') }}</h3>
            <div class="card-tools">
                <a href="{{ route('teams.create') }}" class="btn btn-primary">
                    {{ __('messages.create') }} {{ __('messages.team') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('messages.team_name') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop
```

### Add New Keys
```php
// lang/en/messages.php
'team_name' => 'Team Name',

// lang/id/messages.php  
'team_name' => 'Nama Tim',
```

## 🚀 Pro Tips

### 1. Use VS Code Find & Replace with Regex
Find: `>(Teams|Racers|Cards)<`
Replace: `>{{ __('messages.\L$1\E') }}<`

### 2. Group Related Translations
```php
// Instead of flat structure
'team_name' => 'Team Name',
'team_create' => 'Create Team',

// Use dot notation (create new file lang/en/team.php)
'name' => 'Team Name',
'create' => 'Create Team',

// Usage: __('team.name'), __('team.create')
```

### 3. Reuse Common Translations
```blade
{{-- Instead of duplicating --}}
{{ __('messages.save') }} {{-- Used everywhere --}}

{{-- Not this --}}
{{ __('messages.save_team') }}
{{ __('messages.save_racer') }}
{{ __('messages.save_card') }}
```

### 4. Test Both Languages Immediately
After converting a page:
1. Visit the page
2. Switch to Indonesian
3. Verify all text changed
4. Check for any hardcoded text that was missed

## ⚠️ Common Mistakes to Avoid

### ❌ Wrong
```blade
{{ __('Dashboard') }}  {{-- Missing file prefix --}}
{{ __('messages.Dashboard') }}  {{-- Keys should be lowercase --}}
{{ __("messages.save") }}  {{-- Use single quotes for keys --}}
__('messages.save')  {{-- Missing echo braces --}}
```

### ✅ Correct
```blade
{{ __('messages.dashboard') }}
{{ __('messages.save') }}
```

## 🎨 CSS Classes with Translations
```blade
<span class="badge badge-success">
    {{ __('messages.active') }}
</span>

<span class="badge badge-danger">
    {{ __('messages.cancelled') }}
</span>
```

## 🔄 Dynamic Content
```blade
{{-- Don't translate dynamic database values --}}
<td>{{ $team->team_name }}</td>  {{-- Keep as is --}}

{{-- Translate labels and static text --}}
<th>{{ __('messages.team_name') }}</th>
```

## 📞 Get Current Language in PHP
```php
$currentLang = app()->getLocale();  // 'en' or 'id'

if ($currentLang === 'id') {
    // Indonesian-specific logic
}
```

## 🎯 Priority Order for Translation

1. **High Priority** (User-facing text):
   - Page titles and headers
   - Button labels
   - Form labels
   - Error/success messages

2. **Medium Priority**:
   - Table headers
   - Card titles
   - Navigation items

3. **Low Priority**:
   - Tooltips
   - Placeholder text
   - Help text

Start with high priority items first!

## ✅ Checklist for Each Page

- [ ] Added language switcher to navbar
- [ ] Converted page title
- [ ] Converted all headings
- [ ] Converted all buttons
- [ ] Converted all form labels
- [ ] Converted table headers
- [ ] Converted status badges
- [ ] Converted modal text (if any)
- [ ] Added new keys to both en and id files
- [ ] Tested in both languages
- [ ] No console errors
- [ ] All text changes when switching language

---

**Need help?** Check `MULTI_LANGUAGE_GUIDE.md` for detailed documentation.

