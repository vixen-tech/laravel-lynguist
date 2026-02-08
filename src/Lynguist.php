<?php

namespace Vixen\Lynguist;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Lynguist
{
    /**
     * Get all scannable files from the given directories.
     *
     * @return Collection<int, \SplFileInfo>
     */
    public function getScannableFiles(string|array $dirs): Collection
    {
        $dirs = is_array($dirs) ? $dirs : [$dirs];
        $allowedExtensions = config('lynguist.allowed_extensions');

        return collect($dirs)
            ->flatMap(fn (string $dir) => File::allFiles($dir))
            ->filter(fn (\SplFileInfo $file) => ! $allowedExtensions || in_array($file->getExtension(), $allowedExtensions))
            ->values();
    }

    /**
     * Scan directories for translation terms.
     *
     * @param  callable|null  $onProgress  Called after each file is processed with (current, total) parameters.
     */
    public function scan(string|array $dirs, ?callable $onProgress = null): Collection
    {
        $files = $this->getScannableFiles($dirs);
        $terms = collect();
        $total = $files->count();
        $current = 0;

        foreach ($files as $file) {
            $contents = File::get($file);

            $terms = $terms
                ->merge($this->extractFrom($contents))
                ->unique();

            $current++;

            if ($onProgress) {
                $onProgress($current, $total);
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

    /**
     * Extract translation terms from file contents.
     */
    public function extractFrom(string $text): Collection
    {
        $search = config('lynguist.search_for');
        $search = join('|', $search);

        preg_match_all("/(?:{$search})\\s*\\(\\s*(['\"])((?:(?!\\1|\\\\).|\\\\.)*)?\\1/us", $text, $matches);

        return collect($matches[2] ?? []);
    }
}
