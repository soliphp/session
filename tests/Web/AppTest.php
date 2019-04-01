<?php

namespace Soli\Tests\Web;

use PHPUnit\Framework\TestCase;

use Soli\Di\Container;
use Soli\Events\Event;
use Soli\Web\App;
use Soli\Web\Router;

class AppTest extends TestCase
{
    /** @var \Soli\Web\App */
    protected $app;

    /** @var \Soli\Di\ContainerInterface */
    protected static $container;

    public static function setUpBeforeClass()
    {
        static::$container = new Container();
    }

    public static function tearDownAfterClass()
    {
        static::$container = null;
    }

    public function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'TEST';

        static::$container->set('router', function () {
            $router = new Router();
            $router->setDefaults([
                // 控制器的命名空间
                'namespace' => "Soli\\Tests\\Handlers\\",
                'controller' => "index",
                'action' => "index",
                'params' => []
            ]);

            $routesConfig = [
                ['index/responseInstance', ['action' => 'responseInstance'], 'TEST'],
                ['index/hello/{name}', ['action' => 'hello'], 'TEST'],
                ['index/responseFalse', ['action' => 'responseFalse'], 'TEST'],
                ['index/normal', ['action' => 'normal'], 'TEST'],
                ['index/responseJson', ['action' => 'responseJson'], 'TEST'],
                ['index/typeError/{id}', ['action' => 'typeError'], 'TEST'],
                ['index/handleException', ['action' => 'handleException'], 'TEST'],
            ];
            $router->load($routesConfig);

            return $router;
        });

        $this->app = new App();
    }

    public function testHandleDefault()
    {
        $uri = '/';
        $response = $this->app->handle($uri);
        $this->assertEquals('Hello, Soli.', $response->getContent());
    }

    public function testResponseInstance()
    {
        $app = $this->app;
        $app->response->reset();

        $response = $app->handle('index/responseInstance');
        $this->assertEquals('response content', $response->getContent());
    }

    public function testResponseString()
    {
        $app = $this->app;
        $app->response->reset();

        $response = $app->handle('index/hello/Soli');
        $app->terminate();
        $this->assertEquals('Hello, Soli.', $response->getContent());
    }

    public function testResponseFalse()
    {
        $app = $this->app;
        $app->response->reset();

        $response = $app->handle('index/responseFalse');
        $this->assertNull($response->getContent());
    }

    public function testResponseNormal()
    {
        $app = $this->app;
        $app->response->reset();

        $response = $app->handle('index/normal');
        $this->assertNull($response->getContent());
    }

    public function testResponseJson()
    {
        $app = $this->app;
        $app->response->reset();

        $response = $app->handle('index/responseJson');
        $this->assertTrue(is_json($response->getContent()));
    }

    /**
     * @expectedException \Exception
     */
    public function testUnCatchException()
    {
        $app = $this->app;
        $app->response->reset();

        $app->handle('index/notfoundxxxxxxx');
    }

    public function testCatchCallActionExceptionEvent()
    {
        $app = $this->app;
        $app->response->reset();

        $exceptionResponseContent = 'Handled Exception: TypeError';
        $this->setEventManager($app, $exceptionResponseContent);
        $response = $app->handle('index/typeError/should-be-int');
        $this->assertEquals($exceptionResponseContent, $response->getContent());
    }

    public function testCatchNotFoundExceptionEvent()
    {
        $app = $this->app;
        $app->response->reset();

        $exceptionResponseContent = 'Handled Exception: not found action';
        $this->setEventManager($app, $exceptionResponseContent);
        $response = $app->handle('index/notfoundxxxxxxx');
        $this->assertEquals($exceptionResponseContent, $response->getContent());
    }

    protected function setEventManager(App $app, $response)
    {
        $eventManager = $app->events;
        $eventManager->attach(
            'app.exception',
            function (Event $event, App $app, \Throwable $exception) use ($response) {
                // exception handling
                $app->dispatcher->forward([
                    'namespace'  => "Soli\\Tests\\Handlers\\",
                    'handler'    => 'index',
                    'action'     => 'handleException',
                    'params'     => [$response]
                ]);
                return $app->dispatcher->dispatch();
            }
        );
    }
}
