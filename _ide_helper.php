<?php
/* @noinspection ALL */
// @formatter:off
// phpcs:ignoreFile

/**
 * A helper file for Laravel, to provide autocomplete information to your IDE
 * Generated for Laravel 12.25.0.
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 * @see https://github.com/barryvdh/laravel-ide-helper
 */
namespace Intervention\Image\Facades {
    /**
     */
    class Image {
        /**
         * Overrides configuration settings
         *
         * @param array $config
         * @return self
         * @static
         */
        public static function configure($config = [])
        {
            /** @var \Intervention\Image\ImageManager $instance */
            return $instance->configure($config);
        }

        /**
         * Initiates an Image instance from different input types
         *
         * @param mixed $data
         * @return \Intervention\Image\Image
         * @static
         */
        public static function make($data)
        {
            /** @var \Intervention\Image\ImageManager $instance */
            return $instance->make($data);
        }

        /**
         * Creates an empty image canvas
         *
         * @param int $width
         * @param int $height
         * @param mixed $background
         * @return \Intervention\Image\Image
         * @static
         */
        public static function canvas($width, $height, $background = null)
        {
            /** @var \Intervention\Image\ImageManager $instance */
            return $instance->canvas($width, $height, $background);
        }

        /**
         * Create new cached image and run callback
         * (requires additional package intervention/imagecache)
         *
         * @param \Closure $callback
         * @param int $lifetime
         * @param boolean $returnObj
         * @return \Image
         * @static
         */
        public static function cache($callback, $lifetime = null, $returnObj = false)
        {
            /** @var \Intervention\Image\ImageManager $instance */
            return $instance->cache($callback, $lifetime, $returnObj);
        }

            }
    }

namespace Barryvdh\DomPDF\Facade {
    /**
     * @method static BasePDF setBaseHost(string $baseHost)
     * @method static BasePDF setBasePath(string $basePath)
     * @method static BasePDF setCanvas(\Dompdf\Canvas $canvas)
     * @method static BasePDF setCallbacks(array<string, mixed> $callbacks)
     * @method static BasePDF setCss(\Dompdf\Css\Stylesheet $css)
     * @method static BasePDF setDefaultView(string $defaultView, array<string, mixed> $options)
     * @method static BasePDF setDom(\DOMDocument $dom)
     * @method static BasePDF setFontMetrics(\Dompdf\FontMetrics $fontMetrics)
     * @method static BasePDF setHttpContext(resource|array<string, mixed> $httpContext)
     * @method static BasePDF setPaper(string|float[] $paper, string $orientation = 'portrait')
     * @method static BasePDF setProtocol(string $protocol)
     * @method static BasePDF setTree(\Dompdf\Frame\FrameTree $tree)
     */
    class Pdf {
        /**
         * Get the DomPDF instance
         *
         * @static
         */
        public static function getDomPDF()
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->getDomPDF();
        }

        /**
         * Show or hide warnings
         *
         * @static
         */
        public static function setWarnings($warnings)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->setWarnings($warnings);
        }

        /**
         * Load a HTML string
         *
         * @param string|null $encoding Not used yet
         * @static
         */
        public static function loadHTML($string, $encoding = null)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->loadHTML($string, $encoding);
        }

        /**
         * Load a HTML file
         *
         * @static
         */
        public static function loadFile($file)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->loadFile($file);
        }

        /**
         * Add metadata info
         *
         * @param array<string, string> $info
         * @static
         */
        public static function addInfo($info)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->addInfo($info);
        }

        /**
         * Load a View and convert to HTML
         *
         * @param array<string, mixed> $data
         * @param array<string, mixed> $mergeData
         * @param string|null $encoding Not used yet
         * @static
         */
        public static function loadView($view, $data = [], $mergeData = [], $encoding = null)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->loadView($view, $data, $mergeData, $encoding);
        }

        /**
         * Set/Change an option (or array of options) in Dompdf
         *
         * @param array<string, mixed>|string $attribute
         * @param null|mixed $value
         * @static
         */
        public static function setOption($attribute, $value = null)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->setOption($attribute, $value);
        }

        /**
         * Replace all the Options from DomPDF
         *
         * @param array<string, mixed> $options
         * @static
         */
        public static function setOptions($options, $mergeWithDefaults = false)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->setOptions($options, $mergeWithDefaults);
        }

        /**
         * Output the PDF as a string.
         * 
         * The options parameter controls the output. Accepted options are:
         * 
         * 'compress' = > 1 or 0 - apply content stream compression, this is
         *    on (1) by default
         *
         * @param array<string, int> $options
         * @return string The rendered PDF as string
         * @static
         */
        public static function output($options = [])
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->output($options);
        }

        /**
         * Save the PDF to a file
         *
         * @static
         */
        public static function save($filename, $disk = null)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->save($filename, $disk);
        }

        /**
         * Make the PDF downloadable by the user
         *
         * @static
         */
        public static function download($filename = 'document.pdf')
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->download($filename);
        }

        /**
         * Return a response with the PDF to show in the browser
         *
         * @static
         */
        public static function stream($filename = 'document.pdf')
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->stream($filename);
        }

        /**
         * Render the PDF
         *
         * @static
         */
        public static function render()
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->render();
        }

        /**
         * @param array<string> $pc
         * @static
         */
        public static function setEncryption($password, $ownerpassword = '', $pc = [])
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->setEncryption($password, $ownerpassword, $pc);
        }

            }
    /**
     * @method static BasePDF setBaseHost(string $baseHost)
     * @method static BasePDF setBasePath(string $basePath)
     * @method static BasePDF setCanvas(\Dompdf\Canvas $canvas)
     * @method static BasePDF setCallbacks(array<string, mixed> $callbacks)
     * @method static BasePDF setCss(\Dompdf\Css\Stylesheet $css)
     * @method static BasePDF setDefaultView(string $defaultView, array<string, mixed> $options)
     * @method static BasePDF setDom(\DOMDocument $dom)
     * @method static BasePDF setFontMetrics(\Dompdf\FontMetrics $fontMetrics)
     * @method static BasePDF setHttpContext(resource|array<string, mixed> $httpContext)
     * @method static BasePDF setPaper(string|float[] $paper, string $orientation = 'portrait')
     * @method static BasePDF setProtocol(string $protocol)
     * @method static BasePDF setTree(\Dompdf\Frame\FrameTree $tree)
     */
    class Pdf {
        /**
         * Get the DomPDF instance
         *
         * @static
         */
        public static function getDomPDF()
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->getDomPDF();
        }

        /**
         * Show or hide warnings
         *
         * @static
         */
        public static function setWarnings($warnings)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->setWarnings($warnings);
        }

        /**
         * Load a HTML string
         *
         * @param string|null $encoding Not used yet
         * @static
         */
        public static function loadHTML($string, $encoding = null)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->loadHTML($string, $encoding);
        }

        /**
         * Load a HTML file
         *
         * @static
         */
        public static function loadFile($file)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->loadFile($file);
        }

        /**
         * Add metadata info
         *
         * @param array<string, string> $info
         * @static
         */
        public static function addInfo($info)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->addInfo($info);
        }

        /**
         * Load a View and convert to HTML
         *
         * @param array<string, mixed> $data
         * @param array<string, mixed> $mergeData
         * @param string|null $encoding Not used yet
         * @static
         */
        public static function loadView($view, $data = [], $mergeData = [], $encoding = null)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->loadView($view, $data, $mergeData, $encoding);
        }

        /**
         * Set/Change an option (or array of options) in Dompdf
         *
         * @param array<string, mixed>|string $attribute
         * @param null|mixed $value
         * @static
         */
        public static function setOption($attribute, $value = null)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->setOption($attribute, $value);
        }

        /**
         * Replace all the Options from DomPDF
         *
         * @param array<string, mixed> $options
         * @static
         */
        public static function setOptions($options, $mergeWithDefaults = false)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->setOptions($options, $mergeWithDefaults);
        }

        /**
         * Output the PDF as a string.
         * 
         * The options parameter controls the output. Accepted options are:
         * 
         * 'compress' = > 1 or 0 - apply content stream compression, this is
         *    on (1) by default
         *
         * @param array<string, int> $options
         * @return string The rendered PDF as string
         * @static
         */
        public static function output($options = [])
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->output($options);
        }

        /**
         * Save the PDF to a file
         *
         * @static
         */
        public static function save($filename, $disk = null)
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->save($filename, $disk);
        }

        /**
         * Make the PDF downloadable by the user
         *
         * @static
         */
        public static function download($filename = 'document.pdf')
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->download($filename);
        }

        /**
         * Return a response with the PDF to show in the browser
         *
         * @static
         */
        public static function stream($filename = 'document.pdf')
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->stream($filename);
        }

        /**
         * Render the PDF
         *
         * @static
         */
        public static function render()
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->render();
        }

        /**
         * @param array<string> $pc
         * @static
         */
        public static function setEncryption($password, $ownerpassword = '', $pc = [])
        {
            /** @var \Barryvdh\DomPDF\PDF $instance */
            return $instance->setEncryption($password, $ownerpassword, $pc);
        }

            }
    }

namespace Livewire {
    /**
     * @see \Livewire\LivewireManager
     */
    class Livewire extends \Livewire\LivewireManager {
        /**
         * {@inheritDoc}
         *
         * @static
         */
        public static function mount($name, $params = [], $key = null)
        {
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->mount($name, $params, $key);
        }

        /**
         * {@inheritDoc}
         *
         * @static
         */
        public static function update($snapshot, $diff, $calls)
        {
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->update($snapshot, $diff, $calls);
        }

        /**
         * @static
         */
        public static function setProvider($provider)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->setProvider($provider);
        }

        /**
         * @static
         */
        public static function provide($callback)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->provide($callback);
        }

        /**
         * @static
         */
        public static function component($name, $class = null)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->component($name, $class);
        }

        /**
         * @static
         */
        public static function componentHook($hook)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->componentHook($hook);
        }

        /**
         * @static
         */
        public static function propertySynthesizer($synth)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->propertySynthesizer($synth);
        }

        /**
         * @static
         */
        public static function directive($name, $callback)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->directive($name, $callback);
        }

        /**
         * @static
         */
        public static function precompiler($callback)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->precompiler($callback);
        }

        /**
         * @static
         */
        public static function new($name, $id = null)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->new($name, $id);
        }

        /**
         * @static
         */
        public static function isDiscoverable($componentNameOrClass)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->isDiscoverable($componentNameOrClass);
        }

        /**
         * @static
         */
        public static function resolveMissingComponent($resolver)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->resolveMissingComponent($resolver);
        }

        /**
         * @static
         */
        public static function snapshot($component)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->snapshot($component);
        }

        /**
         * @static
         */
        public static function fromSnapshot($snapshot)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->fromSnapshot($snapshot);
        }

        /**
         * @static
         */
        public static function listen($eventName, $callback)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->listen($eventName, $callback);
        }

        /**
         * @static
         */
        public static function current()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->current();
        }

        /**
         * @static
         */
        public static function findSynth($keyOrTarget, $component)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->findSynth($keyOrTarget, $component);
        }

        /**
         * @static
         */
        public static function updateProperty($component, $path, $value)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->updateProperty($component, $path, $value);
        }

        /**
         * @static
         */
        public static function isLivewireRequest()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->isLivewireRequest();
        }

        /**
         * @static
         */
        public static function componentHasBeenRendered()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->componentHasBeenRendered();
        }

        /**
         * @static
         */
        public static function forceAssetInjection()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->forceAssetInjection();
        }

        /**
         * @static
         */
        public static function setUpdateRoute($callback)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->setUpdateRoute($callback);
        }

        /**
         * @static
         */
        public static function getUpdateUri()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->getUpdateUri();
        }

        /**
         * @static
         */
        public static function setScriptRoute($callback)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->setScriptRoute($callback);
        }

        /**
         * @static
         */
        public static function useScriptTagAttributes($attributes)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->useScriptTagAttributes($attributes);
        }

        /**
         * @static
         */
        public static function withUrlParams($params)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->withUrlParams($params);
        }

        /**
         * @static
         */
        public static function withQueryParams($params)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->withQueryParams($params);
        }

        /**
         * @static
         */
        public static function withCookie($name, $value)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->withCookie($name, $value);
        }

        /**
         * @static
         */
        public static function withCookies($cookies)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->withCookies($cookies);
        }

        /**
         * @static
         */
        public static function withHeaders($headers)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->withHeaders($headers);
        }

        /**
         * @static
         */
        public static function withoutLazyLoading()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->withoutLazyLoading();
        }

        /**
         * @static
         */
        public static function test($name, $params = [])
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->test($name, $params);
        }

        /**
         * @static
         */
        public static function visit($name)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->visit($name);
        }

        /**
         * @static
         */
        public static function actingAs($user, $driver = null)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->actingAs($user, $driver);
        }

        /**
         * @static
         */
        public static function isRunningServerless()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->isRunningServerless();
        }

        /**
         * @static
         */
        public static function addPersistentMiddleware($middleware)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->addPersistentMiddleware($middleware);
        }

        /**
         * @static
         */
        public static function setPersistentMiddleware($middleware)
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->setPersistentMiddleware($middleware);
        }

        /**
         * @static
         */
        public static function getPersistentMiddleware()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->getPersistentMiddleware();
        }

        /**
         * @static
         */
        public static function flushState()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->flushState();
        }

        /**
         * @static
         */
        public static function originalUrl()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->originalUrl();
        }

        /**
         * @static
         */
        public static function originalPath()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->originalPath();
        }

        /**
         * @static
         */
        public static function originalMethod()
        {
            //Method inherited from \Livewire\LivewireManager 
            /** @var \Livewire\Volt\LivewireManager $instance */
            return $instance->originalMethod();
        }

            }
    }

namespace Laratrust {
    /**
     */
    class LaratrustFacade {
        /**
         * Checks if the current user has a role by its name.
         *
         * @static
         */
        public static function hasRole($role, $team = null, $requireAll = false)
        {
            /** @var \Laratrust\Laratrust $instance */
            return $instance->hasRole($role, $team, $requireAll);
        }

        /**
         * Check if the current user has a permission by its name.
         *
         * @static
         */
        public static function hasPermission($permission, $team = null, $requireAll = false)
        {
            /** @var \Laratrust\Laratrust $instance */
            return $instance->hasPermission($permission, $team, $requireAll);
        }

        /**
         * Check if the current user does not have a permission by its name.
         *
         * @static
         */
        public static function doesntHavePermission($permission, $team = null, $requireAll = false)
        {
            /** @var \Laratrust\Laratrust $instance */
            return $instance->doesntHavePermission($permission, $team, $requireAll);
        }

        /**
         * Check if the current user has a permission by its name.
         * 
         * Alias to hasPermission.
         *
         * @static
         */
        public static function isAbleTo($permission, $team = null, $requireAll = false)
        {
            /** @var \Laratrust\Laratrust $instance */
            return $instance->isAbleTo($permission, $team, $requireAll);
        }

        /**
         * Check if the current user does not have a permission by its name.
         * 
         * Alias to doesntHavePermission.
         *
         * @static
         */
        public static function isNotAbleTo($permission, $team = null, $requireAll = false)
        {
            /** @var \Laratrust\Laratrust $instance */
            return $instance->isNotAbleTo($permission, $team, $requireAll);
        }

        /**
         * Check if the current user has a role or permission by its name.
         *
         * @param array|string $roles The role(s) needed.
         * @param array|string $permissions The permission(s) needed.
         * @param array $options The Options.
         * @return bool
         * @static
         */
        public static function ability($roles, $permissions, $team = null, $options = [])
        {
            /** @var \Laratrust\Laratrust $instance */
            return $instance->ability($roles, $permissions, $team, $options);
        }

            }
    }

namespace Illuminate\Http {
    /**
     */
    class Request extends \Symfony\Component\HttpFoundation\Request {
        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
         * @param array $rules
         * @param mixed $params
         * @static
         */
        public static function validate($rules, ...$params)
        {
            return \Illuminate\Http\Request::validate($rules, ...$params);
        }

        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
         * @param string $errorBag
         * @param array $rules
         * @param mixed $params
         * @static
         */
        public static function validateWithBag($errorBag, $rules, ...$params)
        {
            return \Illuminate\Http\Request::validateWithBag($errorBag, $rules, ...$params);
        }

        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @param mixed $absolute
         * @static
         */
        public static function hasValidSignature($absolute = true)
        {
            return \Illuminate\Http\Request::hasValidSignature($absolute);
        }

        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @static
         */
        public static function hasValidRelativeSignature()
        {
            return \Illuminate\Http\Request::hasValidRelativeSignature();
        }

        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @param mixed $ignoreQuery
         * @param mixed $absolute
         * @static
         */
        public static function hasValidSignatureWhileIgnoring($ignoreQuery = [], $absolute = true)
        {
            return \Illuminate\Http\Request::hasValidSignatureWhileIgnoring($ignoreQuery, $absolute);
        }

        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @param mixed $ignoreQuery
         * @static
         */
        public static function hasValidRelativeSignatureWhileIgnoring($ignoreQuery = [])
        {
            return \Illuminate\Http\Request::hasValidRelativeSignatureWhileIgnoring($ignoreQuery);
        }

            }
    }

namespace Illuminate\Validation {
    /**
     */
    class Rule {
        /**
         * @see \Astrotomic\Translatable\TranslatableServiceProvider::register()
         * @param string $model
         * @param string $field
         * @return \Astrotomic\Translatable\Validation\Rules\TranslatableUnique
         * @static
         */
        public static function translatableUnique($model, $field)
        {
            return \Illuminate\Validation\Rule::translatableUnique($model, $field);
        }

        /**
         * @see \Astrotomic\Translatable\TranslatableServiceProvider::register()
         * @param string $model
         * @param string $field
         * @return \Astrotomic\Translatable\Validation\Rules\TranslatableExists
         * @static
         */
        public static function translatableExists($model, $field)
        {
            return \Illuminate\Validation\Rule::translatableExists($model, $field);
        }

            }
    }

namespace Illuminate\Routing {
    /**
     * @mixin \Illuminate\Routing\RouteRegistrar
     */
    class Router {
        /**
         * @see \Laravel\Ui\AuthRouteMethods::auth()
         * @param mixed $options
         * @static
         */
        public static function auth($options = [])
        {
            return \Illuminate\Routing\Router::auth($options);
        }

        /**
         * @see \Laravel\Ui\AuthRouteMethods::resetPassword()
         * @static
         */
        public static function resetPassword()
        {
            return \Illuminate\Routing\Router::resetPassword();
        }

        /**
         * @see \Laravel\Ui\AuthRouteMethods::confirmPassword()
         * @static
         */
        public static function confirmPassword()
        {
            return \Illuminate\Routing\Router::confirmPassword();
        }

        /**
         * @see \Laravel\Ui\AuthRouteMethods::emailVerification()
         * @static
         */
        public static function emailVerification()
        {
            return \Illuminate\Routing\Router::emailVerification();
        }

            }
    /**
     */
    class Route {
        /**
         * @see \Livewire\Features\SupportLazyLoading\SupportLazyLoading::registerRouteMacro()
         * @param mixed $enabled
         * @static
         */
        public static function lazy($enabled = true)
        {
            return \Illuminate\Routing\Route::lazy($enabled);
        }

            }
    }

namespace Illuminate\View {
    /**
     */
    class ComponentAttributeBag {
        /**
         * @see \Livewire\Features\SupportBladeAttributes\SupportBladeAttributes::provide()
         * @param mixed $name
         * @static
         */
        public static function wire($name)
        {
            return \Illuminate\View\ComponentAttributeBag::wire($name);
        }

            }
    /**
     */
    class View {
        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $data
         * @static
         */
        public static function layoutData($data = [])
        {
            return \Illuminate\View\View::layoutData($data);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $section
         * @static
         */
        public static function section($section)
        {
            return \Illuminate\View\View::section($section);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $title
         * @static
         */
        public static function title($title)
        {
            return \Illuminate\View\View::title($title);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $slot
         * @static
         */
        public static function slot($slot)
        {
            return \Illuminate\View\View::slot($slot);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $view
         * @param mixed $params
         * @static
         */
        public static function extends($view, $params = [])
        {
            return \Illuminate\View\View::extends($view, $params);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $view
         * @param mixed $params
         * @static
         */
        public static function layout($view, $params = [])
        {
            return \Illuminate\View\View::layout($view, $params);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param callable $callback
         * @static
         */
        public static function response($callback)
        {
            return \Illuminate\View\View::response($callback);
        }

            }
    }

namespace Illuminate\Testing {
    /**
     * @template TResponse of \Symfony\Component\HttpFoundation\Response
     * @mixin \Illuminate\Http\Response
     */
    class TestResponse {
        /**
         * @see \Livewire\Volt\VoltServiceProvider::registerTestingMacros()
         * @param mixed $component
         * @static
         */
        public static function assertSeeVolt($component)
        {
            return \Illuminate\Testing\TestResponse::assertSeeVolt($component);
        }

        /**
         * @see \Livewire\Volt\VoltServiceProvider::registerTestingMacros()
         * @param mixed $component
         * @static
         */
        public static function assertDontSeeVolt($component)
        {
            return \Illuminate\Testing\TestResponse::assertDontSeeVolt($component);
        }

            }
    }


namespace  {
    class Image extends \Intervention\Image\Facades\Image {}
    class Str extends \Illuminate\Support\Str {}
    class PDF extends \Barryvdh\DomPDF\Facade\Pdf {}
    class Pdf extends \Barryvdh\DomPDF\Facade\Pdf {}
    class Livewire extends \Livewire\Livewire {}
    class Laratrust extends \Laratrust\LaratrustFacade {}
}


namespace Facades\Livewire\Features\SupportFileUploads {
    /**
     * @mixin \Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl     */
    class GenerateSignedUploadUrl extends \Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl {}
}



