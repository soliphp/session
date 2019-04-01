<?php

namespace Soli\Tests\Web;

use PHPUnit\Framework\TestCase;

use Soli\Di\Container;
use Soli\Web\Flash;

class FlashTest extends TestCase
{
    /** @var \Soli\Web\Flash */
    protected $flash;

    /** @var \Soli\Di\Container */
    protected static $container;

    public static function setUpBeforeClass()
    {
        static::$container = Container::instance() ?: new Container();
    }

    public static function tearDownAfterClass()
    {
        static::$container = null;
    }

    protected function setUp()
    {
        $container = static::$container;

        /** @var Flash $flash */
        $flash = $container->get(Flash::class);
        $flash->setCssClasses($this->setProvider());
        $this->flash = $flash;
    }

    protected function tearDown()
    {
        $this->flash = null;
    }

    public function setProvider()
    {
        return [
            'error'   => 'margin alert alert-danger',
            'success' => 'margin alert alert-success',
            'notice'  => 'margin alert alert-info',
            'warning' => 'margin alert alert-warning',
        ];
    }

    public function testGetCssClasses()
    {
        $this->assertEquals($this->setProvider()['error'], $this->flash->getCssClasses()['error']);
    }

    public function testError()
    {
        $this->myExpectOutputString('error', $this->setProvider()['error']);
    }

    public function testNotice()
    {
        $this->myExpectOutputString('notice', $this->setProvider()['notice']);
    }

    public function testWarning()
    {
        $this->myExpectOutputString('warning', $this->setProvider()['warning']);
    }

    public function testSuccess()
    {
        $this->myExpectOutputString('success', $this->setProvider()['success']);
    }

    public function myExpectOutputString($type, $class)
    {
        $this->expectOutputString(sprintf('<div class="%s">%s</div>', $class, $type));

        $this->flash->{$type}($type);
        $this->flash->output();
    }
}
