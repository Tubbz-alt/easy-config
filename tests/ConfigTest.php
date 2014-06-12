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
    private $configContent = array(
        'required_fields' => array(
            'default' => array('summary', 'reporter', 'status'),
            'open' => null,
            'stable' => array('stepsToStabilise')
        ),
        'incident' => array(
            'default' => array(
                'template' => 'incident.twig'
            ),
            'review' => array(
                'template' => 'review.twig',
                'widgets' => array('media')
            )
        ),
        'incident_list' => array(
            'default' => array(
                'template' => 'incidents.twig',
                'widgets' => array()
            )
        )
    );

    public function setUp()
    {
        $this->config = Config::getInstance();
    }

    public function tearDown()
    {
        $this->config->flush();
    }

    public function providerCache()
    {
        return array(
            array('cache' => true),
            array('cache' => false)
        );
    }

    /**
     * @dataProvider providerCache
     * @expectedException Common\Exceptions\FileNotFoundException
     */
    public function testLoadFileUnexistingFile($cache)
    {
        $this->config->setUseCache($cache);
        $this->config->loadConfig(array(__DIR__ . self::$UNEXISTING_FILE));
    }

    /**
     * @dataProvider providerCache
     * @expectedException Common\Exceptions\InvalidConfigFileException
     */
    public function testLoadFileInvalidYaml($cache)
    {
        $this->config->setUseCache($cache);
        $this->config->loadConfig(array(__DIR__ . self::$INVALID_FILE));
    }

    /**
     * @dataProvider providerCache
     */
    public function testLoadValidConfigFile($cache)
    {
        $this->config->setUseCache($cache);
        $this->config->loadConfig(array(__DIR__ . self::$VALID_FILE));

        $this->assertSame($this->configContent, $this->config->fetch());
    }

    /**
     * @dataProvider providerCache
     */
    public function testFlush($cache)
    {
        $this->config->setUseCache($cache);
        $this->config->loadConfig(array(__DIR__ . self::$VALID_FILE));
        $this->config->flush();

        $this->assertSame(array(), $this->config->fetch());
    }

    /**
     * @dataProvider providerCache
     */
    public function testFetch($cache)
    {
        $this->config->setUseCache($cache);
        $this->config->loadConfig(array(__DIR__ . self::$VALID_FILE));

        $this->assertSame(
            $this->configContent['incident_list']['default']['widgets'],
            $this->config->fetch('incident_list', 'default', 'widgets')
        );
    }

    /**
     * @dataProvider providerCache
     * @expectedException Common\Exceptions\KeyNotFoundException
     */
    public function testFetchNotFound($cache)
    {
        $this->config->setUseCache($cache);
        $this->config->loadConfig(array(__DIR__ . self::$VALID_FILE));

        $this->config->fetch('incident_list', 'default', 'something');
    }

    /**
     * @dataProvider providerCache
     */
    public function testReloadConfig($cache)
    {
        $this->config->setUseCache($cache);
        $this->config->loadConfig(array(__DIR__ . self::$VALID_FILE));
        $this->config->flush();
        $this->config->reloadConfig();

        $this->assertSame($this->configContent, $this->config->fetch());
    }
}
