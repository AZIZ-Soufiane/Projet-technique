# Multi-Language Support Implementation

## Summary
Successfully added English/French language support with a Preline-styled language switcher in the navbar.

## Files Created

### Language Files
- `resources/lang/en/messages.php` - English translations
- `resources/lang/fr/messages.php` - French translations

### Middleware
- `app/Http/Middleware/SetLocale.php` - Middleware to persist language preference in session

## Files Modified

### Routes
- `routes/web.php` - Added `locale.switch` route for language switching

### Layout
- `resources/views/layouts/app-layout.blade.php` - Added Preline language switcher dropdown

### Bootstrap
- `bootstrap/app.php` - Registered SetLocale middleware

## Features

✅ **Language Switcher**
- Located in the navbar next to user menu
- Shows flag emoji for visual recognition
- Current language highlighted in dropdown
- Uses Preline dropdown component

✅ **Session Persistence**
- Language preference saved in session
- Automatically applied on page reload
- Falls back to default locale if not set

✅ **Translation Keys**
- All UI elements translated
- Support for admin dashboard
- Support for event pages
- Support for navigation and footer

## Usage

### Adding Translations
To use translations in your Blade templates:
```blade
{{ __('messages.key_name') }}
```

### Switching Languages
Click on the language switcher in the navbar (top right)
- 🇬🇧 English
- 🇫🇷 Français

### Adding New Translations
1. Add key-value pairs to both:
   - `resources/lang/en/messages.php`
   - `resources/lang/fr/messages.php`

2. Use in template:
   ```blade
   {{ __('messages.your_key') }}
   ```

## Configuration
Default locale can be changed in `config/app.php`:
```php
'locale' => 'en', // or 'fr'
```
