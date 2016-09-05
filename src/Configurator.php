<?php

namespace TomPHP\ConfigServiceProvider;

use TomPHP\ConfigServiceProvider\FileReader\FileLocator;
use TomPHP\ConfigServiceProvider\FileReader\ReaderFactory;
use TomPHP\ConfigServiceProvider\Exception\NoMatchingFilesException;
use TomPHP\ConfigServiceProvider\Exception\UnknownSettingException;

final class Configurator
{
    /**
     * @var ApplicationConfig
     */
    private $config;

    /**
     * @var array
     */
    private $settings = [
        'config_prefix'      => 'config',
        'config_separator'   => '.',
        'services_key'       => 'di.services',
        'inflectors_key'     => 'di.inflectors',
        'singleton_services' => false,
    ];

    /**
     * @api
     *
     * @return Configurator
     */
    public static function apply()
    {
        return new self();
    }

    private function __construct()
    {
        $this->config = new ApplicationConfig([]);
    }

    /**
     * @api
     *
     * @param array $config
     *
     * @return Configurator
     */
    public function configFromArray(array $config)
    {
        $this->config->merge($config);

        return $this;
    }

    /**
     * @api
     *
     * @param string $pattern
     *
     * @return Configurator
     */
    public function configFromFiles($pattern)
    {
        $locator = new FileLocator();

        $factory = new ReaderFactory([
            '.json' => 'TomPHP\ConfigServiceProvider\FileReader\JSONFileReader',
            '.php'  => 'TomPHP\ConfigServiceProvider\FileReader\PHPFileReader',
        ]);

        $files = $locator->locate($pattern);

        if (empty($files)) {
            throw NoMatchingFilesException::fromPattern($pattern);
        }

        foreach ($files as $filename) {
            $reader = $factory->create($filename);
            $this->config->merge($reader->read($filename));
        }

        return $this;
    }

    /**
     * @api
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return Configurator
     */
    public function withSetting($name, $value)
    {
        if (!array_key_exists($name, $this->settings)) {
            throw UnknownSettingException::fromSetting($name, array_keys($this->settings));
        }

        $this->settings[$name] = $value;

        return $this;
    }

    /**
     * @api
     *
     * @param object $container
     *
     * @return void
     */
    public function to($container)
    {
        $this->config->setSeparator($this->settings['config_separator']);

        $factory = new ConfiguratorFactory([
            'League\Container\Container' => 'TomPHP\ConfigServiceProvider\League\LeagueContainerAdapter',
            'Pimple\Container'           => 'TomPHP\ConfigServiceProvider\Pimple\PimpleContainerAdapter',
        ]);

        $configurator = $factory->create($container);

        $configurator->addApplicationConfig($this->config, $this->settings['config_prefix']);

        if (isset($this->config[$this->settings['services_key']])) {
            $configurator->addServiceConfig(
                new ServiceConfig($this->config[$this->settings['services_key']], $this->settings['singleton_services'])
            );
        }

        if (isset($this->config[$this->settings['inflectors_key']])) {
            $configurator->addInflectorConfig(new InflectorConfig($this->config[$this->settings['inflectors_key']]));
        }
    }
}