<?php

namespace Vixen\Lynguist;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Lynguist
{
    /**
     * Scan directories for translation terms.
     */
    public function scan(string | array $dirs): Collection
    {
        $dirs = is_array($dirs) ? $dirs : [$dirs];
        $terms = collect();
        $allowedExtensions = config('lynguist.allowed_extensions');

        foreach ($dirs as $dir) {
            $files = File::allFiles($dir);

            foreach ($files as $file) {
                if ($allowedExtensions && ! in_array($file->getExtension(), $allowedExtensions)) {
                    continue;
                }

                $contents = File::get($file);

                $terms = $terms
                    ->merge($this->extractFrom($contents))
                    ->unique();
            }
        }

        return $terms;
    }

    /**
     * Store translation terms in language files.
     *
     * @param Collection<int, string> $terms
     */
    public function store(Collection $terms): void
    {
        $languages = config('lynguist.languages');
        $outputPath = config('lynguist.output_path');

        foreach ($languages as $lang) {
            $path = "{$outputPath}/{$lang}.json";
            $contents = $this->merge($terms, $lang, $path);

            File::put($path, count($contents) === 0 ? "{}\n" : json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");
        }
    }

    /**
     * @param Collection<int, string> $terms
     */
    public function merge(Collection $terms, string $language, ?string $path = null): Collection
    {
        if (! $path) {
            $path = config('lynguist.output_path');
            $path = "{$path}/{$language}.json";
        }

        $contents = collect(File::exists($path) ? json_decode(File::get($path), associative: true) : [])
            ->filter()
            ->intersectByKeys($terms->flip());

        foreach ($terms as $term) {
            if (! $contents->has($term)) {
                $contents[$term] = null;
            }
        }

        return $contents->sortKeys();
    }

    public function generateTypeScriptFile(Collection $terms): void
    {
        if (! config('lynguist.types_path')) return;

        if (File::exists(config('lynguist.types_path'))) {
            File::delete(config('lynguist.types_path'));
        }

        $terms = str($terms->join("': string\n        '"))
            ->prepend("'")
            ->append("': string");

        $output = "import '@vixen-tech/lynguist'\n
declare module '@vixen-tech/lynguist/dist/types' {
    interface LynguistTranslations {
        {$terms}
    }
}\n";

        File::put(config('lynguist.types_path'), $output);
    }

    /**
     * @param string|null $locale A language code ("en", "fr", etc.) or null to use app's current locale.
     */
    public function translations(?string $locale = null): array
    {
        $path = config('lynguist.output_path');
        $locale = $locale ?: app()->getLocale();

        return json_decode(File::get($path . "/{$locale}.json"), associative: true);
    }

    /**
     * @param array<string, list<string>> $translations
     */
    public function sync(array $translations): void
    {
        $outputPath = config('lynguist.output_path');

        foreach ($translations as $lang => $list) {
            $path = "{$outputPath}/{$lang}.json";

            ksort($list);

            File::put($path, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");
        }
    }

    private function extractFrom(string $text): Collection
    {
        $search = config('lynguist.search_for');
        $search = join('|', $search);

        preg_match_all("/(?:{$search})\\s*\\(\\s*(['\"])((?:(?!\\1|\\\\).|\\\\.)*)?\\1/us", $text, $matches);

        return collect($matches[2] ?? []);
    }
}
