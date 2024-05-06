<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Programming\LaravelProgrammingStyle;
use Composer\Autoload\ClassLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Constant;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;
use Nette\PhpGenerator\Property;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Whoops\Exception\ErrorException;

class Code
{
    public const RETURN_CONTENTS = 2;

    public const REPLACE_FILE = 1;

    public const DO_BOTH = 3;

    protected array $phpFiles = [];

    public function __construct(public $files)
    {
        $mappedValues = static::composerMappedClasses();

        if (! is_array($files)) {
            $files = func_get_args();
        }

        foreach ($files as $fileOrNamespace) {
            if (str_contains($fileOrNamespace, '.')) {
                $file = realpath($fileOrNamespace);
            } else {
                $file = realpath($mappedValues[$fileOrNamespace]);
            }

            if (! isset($file)) {
                throw new \DomainException('File '.$file.' not known to composer, if you believe this to be an error, please run composer dump');
            }

            $this->phpFiles[$file] = PhpFile::fromCode(file_get_contents($file));
        }
    }

    public static function with(string $driver): static|LaravelProgrammingStyle
    {
        return match ($driver) {
            'laravel' => new CodeForLaravel([]),
            LaravelProgrammingStyle::class => new LaravelProgrammingStyle([]),
        };
    }

    public static function composerMappedClasses(): array
    {
        /** @var ClassLoader $loader */
        $loader = require base_path('vendor').'/autoload.php';

        return $loader->getClassMap();
    }

    public static function for(string $desiredParentClass): static
    {
        $classmap = require base_path('vendor/composer/autoload_classmap.php');

        $classes = array_key_exists($desiredParentClass, $classmap) ? $classmap[$desiredParentClass] : $desiredParentClass;

        return new static(
            $desiredParentClass,
            $classes
        );
    }

    public function getPrimaryClassType(): ?ClassType
    {
        /** @var PhpFile $first */
        $first = Arr::first($this->phpFiles);

        /** @var ClassType $classInstance */
        return Arr::first($first->getClasses());
    }

    protected function isArray(Property|Constant $property)
    {
        $contents = explode("\n", (string) $property->getValue());

        return $contents[0] === '[' && $contents[count($contents) - 1] === ']';
    }

    public function addValueToProperty(string $property, string $name, mixed $value)
    {
        $this->modifyProperty($property, function (Property $property) use ($name, $value) {
            if (is_callable($value)) {
                $property->setValue(call_user_func($value, $property));

                return;
            }
            $contents = explode("\n", (string) $property->getValue());
            $content = array_values(array_filter($contents));

            if ($contents[0] === '[' && $contents[count($contents) - 1] === ']') {
                // technically an unsafe eval, but you're using code to write code..
                // Soo uhhh... This may be dubious, but also it could just work well?
                $literal = eval('return '.$property->getValue().';');
                // This really isn't ideal. but we are ensuring the contents are likely an array               if (is_array($literal)) {
                // Since it's an array, we should look for the $name, and then insert that right below it
                // Or if it evaluates to an KV array, we can set the value instead.
                if (is_array($literal[$name])) {
                    // This would be an array of arrays, see `EventServiceProvider`
                    $content = array_values(array_filter($contents));

                    foreach ($content as $lineNumber => $line) {
                        if (str_contains($line, $name)) {
                            $lineWithEvent = $lineNumber;
                        }
                    }

                    if ($lineWithEvent === null) {
                        $content = array_merge(array_slice($content, 0, 1), [
                            // new line with our listener,
                            "    /*(n*/\\$name::class => [",
                            "        /*(n*/\\$value::class.'@handle', // code: this is an autogenerated line",
                            '    ],',
                        ], array_slice($content, 1, count($content)));
                    } else {
                        $content = array_merge(array_slice($content, 0, $lineWithEvent + 1), [
                            // new line with our listener,
                            "        /*(n*/\\$value::class . '@handle', // code: this is an autogenerated line",
                        ], array_slice($content, $lineWithEvent + 1, count($content)));
                    }
                } else {
                    // This could be an array of strings, objects, or basically anything that isn't an array.
                    $content = array_values(array_filter($contents));
                    foreach ($content as $lineNumber => $line) {
                        if (str_contains($line, $name)) {
                            $lineWithEvent = $lineNumber;
                        }
                    }
                    dd($content, $lineWithEvent);

                    //                    if ($lineWithEvent === null) {
                    //                        $content = array_merge(array_slice($content, 0, 1), [
                    //                            // new line with our listener,
                    //                            "    /*(n*/\\$name::class => [",
                    //                            "        /*(n*/\\$value::class.'@handle', // code: this is an autogenerated line",
                    //                            "    ],"
                    //                        ], array_slice($content, 1, count($content)));
                    //                    } else {
                    //                        $content = array_merge(array_slice($content, 0, $lineWithEvent+1), [
                    //                            // new line with our listener,
                    //                            "        /*(n*/\\$value::class . '@handle', // code: this is an autogenerated line"
                    //                        ], array_slice($content, $lineWithEvent+1, count($content)));
                    //                    }
                    // I
                }
            }

            $property->setValue((new Literal(implode("\n", $content))));
        });

        return $this;
    }

    public static function instancesOf(string $desiredParentClass): static
    {
        return cache()->rememberForever('instanceOfCache.'.$desiredParentClass, function () use ($desiredParentClass) {
            // Classes known to composer in array form
            $traits = [];
            $classes = [];
            $interfaces = [];

            foreach (static::composerMappedClasses() as $className => $filePath) {
                $filePath = realpath($filePath);

                if ($filePath === false) {
                    continue;
                }

                if (stripos($className, 'reflection') !== false) {
                    // The class has reflection in the name, generally speaking, I'd like to avoid those...
                    continue;
                }
                if (stripos($className, 'abstract') !== false) {
                    // The class has reflection in the name, generally speaking, I'd like to avoid those...
                    continue;
                }

                $vendorParts = explode('/vendor/', $filePath);

                if (str_contains($filePath, 'vendor/')) {
                    $possibleVendor = explode('/', $vendorParts[1], 2)[0];

                    if (! empty(config('spork.code.settings.blacklist')) && in_array($possibleVendor, config('spork.code.settings.blacklist'))) {
                        continue;
                    }

                    if (! empty(config('spork.code.settings.whitelist')) && ! in_array($possibleVendor, config('spork.code.settings.whitelist'))) {
                        continue;
                    }
                }

                try {
                    if (interface_exists($className)) {
                        $interfaces[] = $className;
                    } elseif (class_exists($className)) {
                        $classes[] = $className;
                    } elseif (trait_exists($className)) {
                        $traits[] = $className;
                    }
                } catch (\Throwable|\Error|\ErrorException|ErrorException|\ReflectionException|\Whoops\Exception\ErrorException|\Symfony\Component\ErrorHandler\Error\FatalError|FatalError $e) {
                    // Missing classes based on my experience so far.
                }
            }

            $possibleInstances = match (true) {
                interface_exists($desiredParentClass) => array_values(array_filter(array_merge($interfaces, $classes), fn ($declaredClass) => isset(class_implements($declaredClass)[$desiredParentClass]))),
                class_exists($desiredParentClass) => array_values(array_filter($classes, fn ($declaredClass) => is_subclass_of($declaredClass, $desiredParentClass))),
                trait_exists($desiredParentClass) => array_values(array_filter(array_merge($traits, $classes), fn ($declaredClass) => in_array($desiredParentClass, trait_uses_recursive($declaredClass)))),
                default => dd($desiredParentClass),
            };

            return new static($possibleInstances);
        });
    }

    public function import(string|array $fqns): static
    {
        $imports = is_array($fqns) ? $fqns : func_get_args();
        foreach ($this->phpFiles as $phpFile) {
            foreach ($phpFile->getNamespaces() as $namespaceName => $namespace) {
                foreach ($imports as $import) {
                    $possibleImport = class_basename($import);
                    $uses = $namespace->getUses();
                    // contains use ClassName;
                    // it could be aliased
                    if (in_array($import, $uses)) {
                        // already imported;
                        continue;
                    }

                    if (isset($uses[$possibleImport])) {
                        // Not imported, but the import already exists, so we need to alias it.
                        // idk how I want to handle this yet, so I'ma just leave this for future me...

                        continue;
                    }

                    $traitBaseName = class_basename($import);

                    if ($namespace->getName().'\\'.$traitBaseName === $import) {
                        // Our class is in the same namespace as the class we're importing
                        // So we don't actually need to do anything
                        continue;
                    }
                    $namespace->addUse($import);
                }
            }
        }

        return $this;
    }

    public function use(string|array $fqns): static
    {
        $imports = is_array($fqns) ? $fqns : func_get_args();

        foreach ($imports as $import) {
            $this->import($import);
        }
        foreach ($this->phpFiles as $phpFile) {
            foreach ($phpFile->getNamespaces() as $namespaceName => $namespace) {
                /** @var ClassType $class */
                foreach ($namespace->getClasses() as $class) {
                    $existingTraits = $class->getTraits();
                    foreach ($imports as $import) {
                        if (isset($existingTraits[$import])) {
                            // The trait is already used by the class.
                            continue;
                        }

                        $class->addTrait($import);
                    }
                }
            }
        }

        return $this;
    }

    protected const TYPE_PROPERTY = 'property';

    protected const TYPE_ATTRIBUTES = 'attribute';

    protected const TYPE_TRAIT = 'trait';

    protected const TYPE_CONSTANT = 'constant';

    protected const TYPE_EXTEND = 'extend';

    protected const TYPE_IMPLEMENT = 'implement';

    protected const TYPE_METHOD = 'method';

    protected function modify(
        string $type,
        string $name,
        \Closure $valueResolver
    ): static {
        /** @var PhpFile $file */
        foreach ($this->phpFiles as $file) {
            /** @var PhpNamespace $namespaceObject */
            foreach ($file->getNamespaces() as $namespace => $namespaceObject) {
                // Add code at the namespace level like use statements, declare(strict_types=1);
                /** @var \Nette\PhpGenerator\ClassType $class */
                foreach ($file->getClasses() as $class) {
                    switch ($type) {
                        case static::TYPE_PROPERTY:
                            /** @var \Nette\PhpGenerator\Property $property */
                            foreach ($class->getProperties() as $propertyName => $property) {
                                if ($propertyName === $name) {
                                    call_user_func($valueResolver, $property);
                                }
                            }
                            break;
                        case static::TYPE_ATTRIBUTES:
                            foreach ($class->getAttributes() as $attributeName => $attribute) {
                                if ($attributeName === $name) {
                                    call_user_func($valueResolver, $attribute);
                                }
                            }
                            break;
                        case static::TYPE_TRAIT:
                            foreach ($class->getTraits() as $traitName => $trait) {
                                if ($traitName === $name) {
                                    call_user_func($valueResolver, $trait);
                                }
                            }
                            break;
                        case static::TYPE_CONSTANT:
                            foreach ($class->getConstants() as $constantName => $constant) {
                                if ($constantName === $name) {
                                    call_user_func($valueResolver, $constant);
                                }
                            }
                            break;
                        case static::TYPE_EXTEND:
                            foreach ($class->getExtends() as $extendClass => $extends) {
                                if ($extendClass === $name) {
                                    call_user_func($valueResolver, $extends);
                                }
                            }
                            break;
                        case static::TYPE_IMPLEMENT:
                            foreach ($class->getImplements() as $implementationClass => $implementation) {
                                if ($implementationClass === $name) {
                                    call_user_func($valueResolver, $implementation);
                                }
                            }
                            break;
                        case static::TYPE_METHOD:
                            foreach ($class->getMethods() as $methodName => $method) {
                                if ($methodName === $name) {
                                    call_user_func($valueResolver, $method);
                                }
                            }
                            break;
                        default:
                            throw new \InvalidArgumentException('Unknown argument type, please see me after class for more details');
                    }
                }
            }
        }

        return $this;
    }

    public function modifyProperty(string $name, \Closure $closure)
    {
        $this->modify(static::TYPE_PROPERTY, $name, $closure);

        return $this;
    }

    public function addProperty(string $name, mixed $value = [])
    {
        /** @var PhpFile $file */
        foreach ($this->phpFiles as $file) {
            /** @var PhpNamespace $namespaceObject */
            foreach ($file->getNamespaces() as $namespace => $namespaceObject) {
                // Add code at the namespace level like use statements, declare(strict_types=1);
                /** @var \Nette\PhpGenerator\ClassType $class */
                foreach ($file->getClasses() as $class) {
                    $class->addProperty($name, $value);
                }
            }
        }

        return $this;
    }

    public function extends(string|array $fqns): static
    {
        $imports = is_array($fqns) ? $fqns : func_get_args();

        if (count($imports) > 1) {
            throw new \DomainException('Failed to extend class, classes may only be extended once in this language.');
        }

        foreach ($imports as $import) {
            $this->import($import);
        }
        foreach ($this->phpFiles as $phpFile) {
            foreach ($phpFile->getNamespaces() as $namespaceName => $namespace) {
                /** @var ClassType $class */
                foreach ($namespace->getClasses() as $class) {
                    $existingExtends = $class->getExtends();

                    foreach ($imports as $import) {
                        if (isset($existingExtends[$import])) {
                            // We already extend the class.
                            continue;
                        }

                        $class->setExtends($import);
                    }
                }
            }
        }

        return $this;
    }

    public function getInterfaces()
    {
        $existingInterfaces = [];
        foreach ($this->phpFiles as $phpFile) {
            foreach ($phpFile->getNamespaces() as $namespaceName => $namespace) {
                /** @var ClassType $class */
                foreach ($namespace->getClasses() as $class) {
                    $existingInterfaces = array_merge($existingInterfaces, $class->getImplements());
                }
            }
        }

        return $existingInterfaces;
    }

    public function getProperty(string $property): array
    {
        return array_map(function (PhpFile $phpFile) use ($property) {
            return array_map(fn (PhpNamespace $namespace) => array_map(fn (ClassType $type) => $type->getProperty($property), $namespace->getClasses()), $phpFile->getNamespaces());
        }, $this->phpFiles);
    }

    public function getClasses(): array
    {
        return array_reduce($this->phpFiles, function (array $allClasses, PhpFile $phpFile) {
            return array_reduce(
                $phpFile->getNamespaces(),
                fn (array $result, PhpNamespace $namespace) => array_merge(
                    $result,
                    array_values(array_map(fn (ClassType|InterfaceType $type) => $namespace->getName().'\\'.$type->getName(), $namespace->getClasses())),
                ),
                $allClasses
            );
        }, []);
    }

    public function implements(string|array $fqns): static
    {
        $imports = is_array($fqns) ? $fqns : func_get_args();

        foreach ($imports as $import) {
            $this->import($import);
        }
        foreach ($this->phpFiles as $phpFile) {
            foreach ($phpFile->getNamespaces() as $namespaceName => $namespace) {
                /** @var ClassType $class */
                foreach ($namespace->getClasses() as $class) {
                    $existingInterfaces = $class->getImplements();

                    foreach ($imports as $import) {
                        if (isset($existingInterfaces[$import])) {
                            // We already implement the interface.
                            continue;
                        }

                        $class->addImplement($import);
                    }
                }
            }
        }

        return $this;
    }

    protected function resolveFileOrClassName(string $fileOrClassName)
    {
        if (str_contains($fileOrClassName, '.')) {
            return $fileOrClassName;
        }

        return static::composerMappedClasses()[$fileOrClassName];
    }

    // Default to non-destructive action.
    public function toFile($howToHandleFile = self::RETURN_CONTENTS)
    {
        foreach ($this->phpFiles as $className => $file) {
            $contents = str_replace("\t", '    ', $this->validateFile($file));
            switch ($howToHandleFile) {
                case static::REPLACE_FILE:
                    if (! file_exists($filePath = $this->resolveFileOrClassName($className))) {
                        // Our class name isn't found int composer.
                        // Perhaps we need to run composer:dump
                        continue 2;
                    }
                    file_put_contents($filePath, $contents);
                    break;
                case static::RETURN_CONTENTS:
                default:
                    return $contents;
            }
        }

        return $this;
    }

    protected function validateFile(PhpFile $file)
    {
        $printer = new Printer;
        $printer->indentation = '    ';
        $printer->linesBetweenMethods = 1;

        $processedFile = (string) $printer->printFile($file);

        $lines = explode("\n", $processedFile);
        $location = array_keys(array_filter($lines, fn ($line) => str_starts_with($line, 'namespace')))[0] ?? 0;
        $preLocation = array_slice($lines, 0, $location + 1);
        $postLocation = array_slice($lines, $location + 1, count($lines));

        $filesystem = new Filesystem();
        $filesystem->makeDirectory(storage_path('tmp'), 0755, true, true);
        // How can we validate the file, like make sure there aren't any parsing errors or obvious exceptions.
        $path = storage_path('tmp/'.Str::random(16));

        $filesystem->put($path, $contentToVerify = implode("\n", array_merge($preLocation, [
            sprintf('require "%s/vendor/autoload.php";', base_path()),
        ], $postLocation)));
        try {
            // Generally this isn't safe, but we need to check for syntax issues but we also want to add
            // a require line for the autoloader,
            try {
                exec('php -f '.escapeshellarg($path).' 2>&1', $output, $errorCode);
                $output = implode("\n", $output);
            } catch (SyntaxErrorException $e) {
                return;
            }
            if ($errorCode !== 0) {
                if (str_starts_with($output, 'PHP Fatal error:')) {
                    $line = explode('on line ', $output, 2)[1] ?? '';

                    throw new FatalError($output, $errorCode, [
                        'file' => $path,
                        'line' => explode("\n", $line, 2)[0],
                    ]);
                }

                throw new SyntaxErrorException('There were syntax errors found in the generated file, this shouldn\'t happen. '.$output);
            }

            return $processedFile;
        } finally {
            $filesystem->delete($path);
        }
    }

    public function propertyIncludesValue($field, \Closure $closure)
    {
        call_user_func($closure, $field);

        return false;
    }

    public function saveAs(string $fileName)
    {
        $file = basename($fileName);
        $directory = str_replace($file, '', $fileName);

        (new Filesystem)->makeDirectory(base_path($directory), 0755, true, true);

        file_put_contents(base_path($fileName), $this->toFile());
    }

    public function constructorProperty(string $name)
    {
        return array_map(function (PhpFile $file) use ($name) {
            return array_map(function (ClassType $class) use ($name) {
                return (string) $class->getMethod('__construct')?->getParameter($name)?->getDefaultValue();
            }, $file->getClasses());
        }, $this->phpFiles);
    }
}
