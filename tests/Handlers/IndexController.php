<?php

namespace Soli\Tests\Handlers;

use Soli\Web\Controller;

/**
 * @property \Soli\Web\Response response
 */
class IndexController extends Controller
{
    public function index()
    {
        return 'Hello, Soli.';
    }

    public function hello($name = 'Soli')
    {
        return "Hello, $name.";
    }

    public function forwardToHello()
    {
        return $this->dispatcher->forward([
            'action' => 'hello',
        ]);
    }

    public function responseFalse()
    {
        return false;
    }

    public function responseInstance()
    {
        return $this->response->setContent('response content');
    }

    public function handleException($content)
    {
        return $this->response->setContent($content);
    }

    public function normal()
    {
    }

    public function responseJson()
    {
        return $this->json(['aa' => 11, 'bb' => 22]);
    }

    public function typeError(int $id)
    {
        return $id;
    }
}
