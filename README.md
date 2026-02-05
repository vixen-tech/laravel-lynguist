# Laravel Lynguist

A Laravel package that automatically discovers and manages translation strings in your application. Scan your codebase for translation function calls, generate language files, and optionally sync with [lynguist.com](https://lynguist.com) for collaborative translation management.

> Works in tandem with NPM's package [@vixen-tech/lynguist](https://www.npmjs.com/package/@vixen-tech/lynguist).

## Features

- **Automatic Translation Discovery** - Scans PHP, Blade, JavaScript, Vue, and TypeScript files for translation function calls
- **Multi-Language Support** - Generates and manages JSON language files for multiple languages
- **Smart Merging** - Preserves existing translations when scanning for new strings
- **TypeScript Integration** - Auto-generates TypeScript declaration files for type-safe frontend translations
- **Cloud Sync** - Upload and sync translations with lynguist.com
- **Customizable** - Configure which directories to scan, file extensions to include, and translation functions to detect

## Requirements

- PHP 8.3+
- Laravel 12.x

## Installation

Install the package via Composer:

```bash
composer require vixen/laravel-lynguist
```

The package auto-registers via Laravel's service provider discovery.

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Vixen\Lynguist\LynguistServiceProvider"
```

## Configuration

After publishing, configure the package in `config/lynguist.php`:

```php
return [
    // Default/reference language
    'default_language' => 'en',

    // Languages to generate files for
    'languages' => ['en'],

    // Translation functions to search for
    'search_for' => ['__', 'lang', 'trans', 'trans_choice', 'transChoice', 'choice'],

    // Output directory for language JSON files
    'output_path' => lang_path(),

    // TypeScript declarations file path (set to false to disable)
    'types_path' => resource_path('js/types/translations.d.ts'),

    // Directories to scan for translations
    'scannable_paths' => [
        app_path(),
        resource_path('views'),
        resource_path('js'),
    ],

    // File extensions to scan (null for all files)
    'allowed_extensions' => ['php', 'js', 'jsx', 'ts', 'tsx', 'vue'],

    // Lynguist.com integration
    'connect' => [
        'api_token' => env('LYNGUIST_API_TOKEN'),
        'sync_url' => env('LYNGUIST_SYNC_URL'),
    ],
];
```

> You can include in the config file only altered options, since they are merged with defaults.

## Usage

### Scanning for Translations

Run the scan command to discover all translation strings in your codebase:

```bash
php artisan lynguist:scan
```

This will:
1. Scan configured directories for translation function calls
2. Create/update JSON language files in your `lang/` directory
3. Generate TypeScript declarations (if configured)

To also upload translations to lynguist.com (this requires an API key):

```bash
php artisan lynguist:scan --upload
```

### Supported Translation Functions

The package detects the following translation functions by default:

**PHP/Blade:**
```php
__('welcome-message')
trans('greeting')
trans_choice('items', $count)
lang('settings.timezone')
```

**Blade Directives:**
```blade
@lang('page-title')
@choice('notifications', $count)
```

**JavaScript/Vue:**
```js
__('frontend-string')
trans('greeting', { name: 'Jane' })
transChoice('pluralized-key', count)
```

### Language File Output

The scan creates JSON files for each configured language (e.g., `lang/en.json`):

```json
{
    "greeting": null,
    "items": null,
    "welcome-message": null
}
```

Keys with `null` values are untranslated. Add your translations:

```json
{
    "welcome-message": "Welcome to our application!",
    "greeting": "Hello",
    "items": "{0} No items|{1} One item|[2,*] :count items"
}
```

Existing translations are preserved when re-scanning and then sorted alphabetically.

### Programmatic Usage

You can also use the package programmatically via the facade:

```php
use Vixen\Lynguist\Facades\Lynguist;

// Scan directories for translation terms
$terms = Lynguist::scan([
    app_path(),
    resource_path('views'),
]);

// Store terms to language files
Lynguist::store($terms);

// Get translations for a language
$translations = Lynguist::translations('en');

// Merge new terms with existing translations
$merged = Lynguist::merge($terms, 'en');

// Generate TypeScript declarations
Lynguist::generateTypeScriptFile($terms);
```

## TypeScript Integration

When `types_path` is configured, the package generates TypeScript declarations for type-safe frontend translations:

```typescript
// resources/js/types/translations.d.ts (configurable)
interface LynguistTranslations {
    'greeting': string
    'items': string
    'welcome-message': string
}
```

This enables autocomplete and type checking for translation keys in your frontend code.

## Cloud Sync with Lynguist.com

To sync translations with [lynguist.com](https://lynguist.com):

1. Add your credentials to `.env`:

    ```env
    LYNGUIST_API_TOKEN=your_api_token
    LYNGUIST_SYNC_URL=https://your-sync-webhook-url
    ```

2. Upload existing translations:

    ```bash
    php artisan lynguist:upload
    ```

    Or include the `--upload` flag when scanning:
    
    ```bash
    php artisan lynguist:scan --upload
    ```

3. Create your callback endpoint:

   ```php
   use Illuminate\Http\Request;
   use Vixen\Lynguist\Lynguist;
   
   class SyncController extends Controller
   {
       public function sync(Request $request, Lynguist $lynguist)
       {
           $lynguist->sync($request->input('translations')); 
   
           return response([
               'message' => 'Updated!',
           ]);
       }
   }
   ```

## Custom Translation Functions

To detect custom translation functions, add them to the `search_for` config:

```php
'search_for' => [
    '__',
    'trans',
    'myCustomHelper',
    'Label', // Supports class/attribute names too
],
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
