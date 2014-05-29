<?php

/**
 * Created by PhpStorm.
 * User: tom.koukoulis
 * Date: 29/05/2014
 * Time: 17:03
 */

namespace Common;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Yaml\Parser;

/**
 * Class Conf
 * @package Common
 */
class Conf
{
    const KEYROOT = 'CONF'; //TODO: Add identifier

    /**
     * @private static $configFilePath
     */
    private static $configFilePath;

    public static function clearCache()
    {
        apc_clear_cache();
    }

    /**
     * @param string $section The section of the config file.
     * @param string $key The key of the config section.
     * @return mixed The value of the config key
     * @throws Exception If the key does not exist
     */
    public static function getConfigValue($section, $key)
    {
        if ($key == '') {
            throw new InvalidArgumentException('Conf Exception: Config key can not be the empty string');
        }

        $configSection = self::getConfigSection($section);

        if (array_key_exists($key, $configSection)) {
            return $configSection[$key];
        } else {
            throw new Exception("Conf Exception: Key '$key' in Section [$section] does not exist in file or in cache.");
        }
    }

    /**
     * @param $section
     * @return array of config key, value pairs
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function getConfigSection($section)
    {
        if ($section == '') {
            throw new InvalidArgumentException('Conf Exception: Config section can not be the empty string');
        }

        //apc_clear_cache();

        $config = self::getConfig();

        if (array_key_exists($section, $config)) {
            return $config[$section];
        } else {
            throw new Exception("Conf Exception: Section ($section) does not exist in file or in cache");
        }
    }

    /**
     * @return array|mixed
     */
    public static function getConfig()
    {
        $config = array();

        $configFileExistsInCache = apc_exists(Conf::KEYROOT);

        if ($configFileExistsInCache) {
            $config = apc_fetch(Conf::KEYROOT);
        } else {
            $config = self::readConfigFileAndStoreInCache();
        }

        return $config;
    }

    /**
     * @return array
     */
    private static function readConfigFileAndStoreInCache()
    {
        $yaml = new Parser();
        $config = $yaml->parse(file_get_contents(self::$configFilePath));

        apc_store(Conf::KEYROOT, $config, 0);

        return $config;
    }
}