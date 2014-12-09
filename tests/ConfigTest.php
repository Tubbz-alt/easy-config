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
 * @package EasyConfig\Tests
 */

namespace EasyConfig\Tests;

use PHPUnit_Framework_TestCase;
use EasyConfig\Config;

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
     * @expectedException EasyConfig\Exceptions\FileNotFoundException
     */
    public function testLoadFileUnexistingFile($cache)
    {
        $this->config->setUseCache($cache);
        $this->config->loadConfig(array(__DIR__ . self::$UNEXISTING_FILE));
    }

    /**
     * @dataProvider providerCache
     * @expectedException EasyConfig\Exceptions\InvalidConfigFileException
     */
    public function testLoadFileInvalidYaml($cache)
    {
        $this->config->setUseCache($cache);
        $this->config->loadConfig(array(__DIR__ . self::$INVALID_FILE));
    }

    /**
     * @dataProvider providerCache
     */
    public function testLoadValidConfigFileAsString($cache)
    {
        $this->config->setUseCache($cache);
        $this->config->loadConfig(__DIR__ . self::$VALID_FILE);

        $this->assertSame($this->configContent, $this->config->fetch());
    }

    /**
     * @dataProvider providerCache
     */
    public function testLoadValidConfigFileAsArray($cache)
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
     * @expectedException EasyConfig\Exceptions\KeyNotFoundException
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
