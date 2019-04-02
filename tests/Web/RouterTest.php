<?php

namespace Soli\Tests\Web;

use PHPUnit\Framework\TestCase;

use Soli\Web\Router;

class RouterTest extends TestCase
{
    /** @var \Soli\Web\Router */
    protected $router;

    public function setUp()
    {
        $router = new Router();
        $router->setDefaults([
            // 控制器的命名空间
            'namespace' => "Soli\\Tests\\Handlers\\",
            'controller' => "index",
            'action' => "index",
            'params' => []
        ]);

        $routesConfig = [
            // 使用数组方式注册路由信息
            ['/hello/{name}', $this->routeHandler(), 'GET'],
            // 使用字符串注册路由信息
            ['/index/{page}', 'Soli\Tests\Handlers\Index::index', 'GET'],
            // 以上注册方式也可以简单写成
            ['/ping', 'index::ping', 'GET'],
        ];

        $router->load($routesConfig);

        $this->router = $router;
    }

    public function routeHandler()
    {
        return [
            'namespace' => "Soli\\Tests\\Handlers\\",
            'controller' => "index",
            'action' => "hello",
        ];
    }

    public function testSimpleStringHandler()
    {
        $router = $this->router;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/ping";

        $router->dispatch();

        $this->assertEquals("Soli\\Tests\\Handlers\\", $router->getNamespaceName());
        $this->assertEquals("index", $router->getHandlerName());
        $this->assertEquals('ping', $router->getActionName());
    }

    public function testStringHandler()
    {
        $router = $this->router;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/index/99";

        $router->dispatch();

        $this->assertEquals("Soli\\Tests\\Handlers\\", $router->getNamespaceName());
        $this->assertEquals("Soli\\Tests\\Handlers\\Index", $router->getHandlerName());
        $this->assertEquals('index', $router->getActionName());

        $this->assertEquals('99', $router->getParams()['page']);
    }

    public function testGetters()
    {
        $router = $this->router;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/hello/soliphp";

        $router->dispatch();

        $handler = $this->routeHandler();

        $this->assertEquals($handler['namespace'], $router->getNamespaceName());
        $this->assertEquals($handler['controller'], $router->getHandlerName());
        $this->assertEquals($handler['action'], $router->getActionName());

        $this->assertEquals('soliphp', $router->getParams()['name']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Not found handler
     */
    public function testNotFoundException()
    {
        $this->router->dispatch('/notfoundxxxxxxx');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Method Not Allowed
     */
    public function testMethodNotAllowedException()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->router->dispatch('/hello/soliphp');
    }
}
