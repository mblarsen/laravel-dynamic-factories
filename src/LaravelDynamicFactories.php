<?php

namespace Mblarsen\LaravelDynamicFactories;

use Illuminate\Support\Str;
use InvalidArgumentException;

class LaravelDynamicFactories
{
    protected static $loader;

    protected $path;

    protected $enabled = false;

    /**
     * Create a loader singleton
     */
    public static function getLoader()
    {
        if (self::$loader !== null) {
            return self::$loader;
        }

        return self::$loader = new static;
    }

    /**
     * Enable the loader
     */
    public static function enableLoader()
    {
        static::getLoader()->enable();
    }

    public function __construct()
    {
        // The config is stored at this time before auto-loading is enabled due
        // to ways config is resolved in app().
        $this->_config = config('dynamic-factories');
    }

    /**
     * Start loading factory classes dynamically
     */
    public function enable()
    {
        if (!$this->enabled) {
            spl_autoload_register(
                [$this, 'loadClass'],
                false, // do not throw exception if class is not found
                true, // prepend loader so that it runs before composer
            );
        }
    }

    public function loadClass($class)
    {
        if ($this->matchSuffix($class) && $this->matchesModelNamespaces($class)) {
            $this->ensureClass($class);
            return true;
        }
        return null;
    }

    protected function matchSuffix($class): bool
    {
        $suffix = $this->config('suffix', 'Factory');
        return Str::endsWith($class, $suffix);
    }

    protected function matchesModelNamespaces($class): bool
    {
        $model = $this->getModelName($class);
        $matches = $this->matchModels($model);
        return !empty($matches);
    }

    protected function ensureClass($class)
    {
        $hash = $this->configHash();
        $hash = empty($this->config('path')) || $this->config('hash', false)
            ? '_' . $hash
            : '';
        $file_path = $this->getPath() . class_basename($class) . $hash . '.php';
        if (!file_exists($file_path)) {
            $class_file_content = $this->generateClass($class);
            file_put_contents($file_path, $class_file_content);
        }

        require_once $file_path;
    }

    protected function generateClass($class)
    {
        $namespace = $this->config('namespace', '');

        if (empty($namespace)) {
            $namespace_code = $namespace;
        } else {
            $this->validateNamespace($namespace);
            $namespace_code = "namespace $namespace;";
        }

        $class_name = class_basename($class);

        $extends = '';

        $model_name = $this->getModelName($class);
        $matches = $this->matchModels($model_name);

        $fg_model_name = '\\' . $matches[0];

        $template = $this->loadTemplate();

        return strtr($template, [
            '{{ namespace }}' => $namespace_code,
            '{{ class }}' => $class_name,
            '{{ extends }}' => $extends,
            '{{ model }}' => $fg_model_name
        ]);
    }

    protected function getModelName($class): string
    {
        $suffix = $this->config('suffix', 'Factory');
        return Str::of($class)
            ->beforeLast($suffix)
            ->trim('\\')
            ->afterLast('\\')
            ->trim('\\');
    }

    protected function validateNamespace($namespace): void
    {
        // 0 = match, false = error, 1 = match
        if (!preg_match('~^[A-Za-z0-9_\\\]+$~', $namespace)) {
            throw new InvalidArgumentException('Invalid namespace: ' . $namespace);
        }
    }

    protected function validateClassName($class): void
    {
        // 0 = match, false = error, 1 = match
        if (!preg_match('~^[A-Za-z0-9_\\\]+$~', $class)) {
            throw new InvalidArgumentException('Invalid class name: ' . $class);
        }
    }

    protected function matchModels($model_name): array
    {
        $namespaces = $this->config('model_namespaces', ['App']);
        $matches = [];
        foreach ($namespaces as $namespace) {
            $full_class = $namespace . '\\' . $model_name;

            if (class_exists($full_class)) {
                $matches[] = $full_class;
            }
        }
        return $matches;
    }

    protected function loadTemplate(): string
    {
        return file_get_contents(__DIR__ . '/../src/resources/templates/class.tpl.php');
    }

    protected function getPath()
    {
        if ($this->path === null) {
            $path = $this->config('path', __DIR__ . '/../generated/');

            if (!file_exists($path)) {
                mkdir($path);
            }

            $this->path = $path;
        }

        return $this->path;
    }

    protected function configHash(): string
    {
        $hash_input = join('|', [
            $this->config('path', ''),
            $this->config('suffix', ''),
            $this->config('namespace', ''),
            join(',', $this->config('model_namespaces', [])),
        ]);

        return md5($hash_input);
    }

    protected function config($key, $default = null)
    {
        return $this->_config[$key] ?? $default;
    }
}
