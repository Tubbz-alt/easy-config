<?php

namespace Common\Tests;

use PHPUnit_Framework_TestCase;
use Common\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    private static $VALID_FILE = '/valid-properties.yml';
    private static $INVALID_FILE = '/invalid-properties.yml';
    private static $UNEXISTING_FILE = '/unexisting-properties.yml';
    private $config;

    public function setUp()
    {
        $this->config = Config::getInstance();
    }

    /**
     * @expectedException Common\Exceptions\FileNotFoundException
     */
    public function testLoadFileUnexistingFile()
    {
        $this->config->loadConfig(array(__DIR__ . self::$UNEXISTING_FILE));
    }

    /**
     * @expectedException Common\Exceptions\InvalidConfigFileException
     */
    public function testLoadFileInvalidYaml()
    {
        $this->config->loadConfig(array(__DIR__ . self::$INVALID_FILE));
    }
}
