<?php

namespace Common;

use Exception;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml;
use Common\Exceptions\InvalidConfigFileException;
use Common\Exceptions\FileNotFoundException;
use Common\Exceptions\KeyNotFoundException;

class Config
{
    /**
     * @var Config|null
     */
    private static $instance = null;
    /**
     * @var Yaml\Parser
     */
    private $parser;
    private $configFiles = array();
    private $config = array();
    private $useCache = true;

    private function __construct(Yaml\Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return Config
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new Config(new Yaml\Parser());
        }

        return self::$instance;
    }

    public function loadConfig(array $configFiles)
    {
        $this->configFiles = $configFiles;

        foreach ($configFiles as $configFile) {
            $this->config = array_merge(
                $this->config,
                $this->loadConfigFile($configFile)
            );
        }
    }

    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;
    }

    /**
     * @throws KeyNotFoundException
     */
    public function fetch(/* $key1, $key2, ... $keyN */)
    {
        $keys = func_get_args();

        $config = $this->config;
        foreach ($keys as $key) {
            if (isset($config[$key])) {
                $config = $config[$key];
            } else {
                throw new KeyNotFoundException("Key $key not found.");
            }
        }

        return $config;
    }

    public function reloadConfigFiles()
    {
        $this->flush();

        $this->loadConfigFiles($this->configFiles);
    }

    public function flush()
    {
        if ($this->useCache) {
            foreach ($this->configFiles as $configFile) {
                \apc_delete($configFile);
            }
        }

        $this->config = array();
    }

    /**
     * @throws FileNotFoundException
     * @throws InvalidConfigFileException
     */
    private function loadConfigFile($configFile)
    {
        if ($this->useCache) {
            $config = apc_fetch($configFile);
            if (!empty($config)) {
                return $config;
            }
        }

        if (!is_readable($configFile)) {
            throw new FileNotFoundException("File $configFile could not be found.");
        }

        try {
            $config = $yaml->parse(file_get_contents($configFile));
        } catch (ParseException $e) {
            $line = $e->getParsedLine();
            throw new InvalidConfigFileException(
                "File $configFile does not have a valid format (line $line)"
            );
        }

        if ($this->useCache) {
            apc_store($configFile, $config);
        }

        return $config;
    }
}
