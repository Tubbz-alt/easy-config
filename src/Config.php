<?php

/**
 * Copyright 2014 Shazam Entertainment Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this 
 * file except in compliance with the License.
 *
 * You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under 
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR 
 * CONDITIONS OF ANY KIND, either express or implied. See the License for the specific 
 * language governing permissions and limitations under the License.
 *
 * @author toni lopez <toni.lopez@shazam.com>
 * @package Common
 */

namespace Common;

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

    /**
     * @var $configFiles
     */
    private $configFiles = array();

    /**
     * The data from config files.
     * @var array
     */
    private $config = array();

    /**
     * @param boolean
     */
    private $useCache = true;

    /**
     * @param Yaml\Parser $parser
     */
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

    /**
     * Sets to the config property the merged content from all the config files.
     * @param array $configFiles
     */
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

    /**
     * @param boolean $useCache
     */
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

    /**
     * Flushes cache (if used) and loads again all the config files loaded previously.
     */
    public function reloadConfig()
    {
        $this->flush();

        $this->loadConfig($this->configFiles);
    }

    /**
     * If cache is used, flushes it. Otherwise it just empties config property.
     */
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
     * Loads a config file into config property. If cache is used, first check if the config
     * file was previously loaded.
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
            $config = $this->parser->parse(file_get_contents($configFile));
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
