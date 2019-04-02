<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Web;

use Soli\App as BaseApp;
use Throwable;

/**
 * 应用
 *
 * @property \Soli\RouterInterface $router
 * @property \Soli\Dispatcher $dispatcher
 * @property \Soli\Web\Request $request
 * @property \Soli\Web\Response $response
 * @property \Soli\Web\Session $session
 * @property \Soli\Web\Flash $flash
 * @property \Soli\ViewInterface $view
 */
class App extends BaseApp
{
    /**
     * 默认注册服务
     */
    protected $coreServices = [
        'router'     => [\Soli\Web\Router::class, \Soli\RouterInterface::class],
        'dispatcher' => [\Soli\Dispatcher::class, \Soli\DispatcherInterface::class],
        'events'     => [\Soli\Events\EventManager::class, \Soli\Events\EventManagerInterface::class],
        'request'    => [\Soli\Web\Request::class],
        'response'   => [\Soli\Web\Response::class],
        'session'    => [\Soli\Web\Session::class],
        'flash'      => [\Soli\Web\Flash::class],
    ];

    /**
     * 应用程序启动方法
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @param string $uri
     * @return \Soli\Web\Response
     */
    public function handle($uri = null)
    {
        try {
            $returnedResponse = parent::handle($uri);
            return $this->handleWeb($returnedResponse);
        } catch (Throwable $e) {
            /** @noinspection PhpUnhandledExceptionInspection */
            return $this->handleException($e);
        }
    }

    protected function handleWeb($returnedResponse)
    {
        // 不自动渲染视图的四种方式:
        // 1. 返回 Response 实例
        // 2. 返回 string 类型作为响应内容
        // 3. 返回 false
        // 4. 禁止自动渲染视图 $view->disable()

        if ($returnedResponse instanceof Response) {
            $response = $returnedResponse;
        } else {
            $response = $this->response;
            if (is_string($returnedResponse)) {
                // 作为响应内容
                $response->setContent($returnedResponse);
            } elseif ($returnedResponse !== false) {
                // 渲染视图
                $response->setContent($this->viewRender());
            }
        }

        return $response;
    }

    /**
     * 获取视图自动渲染内容
     *
     * @codeCoverageIgnore
     * @return string
     */
    protected function viewRender()
    {
        if (!$this->container->has('view')) {
            return null;
        }

        // 视图实例
        $view = $this->view;

        // 禁止自动渲染视图
        if ($view->isDisabled()) {
            return null;
        }

        // 获取模版文件路径
        $controller = $this->dispatcher->getHandlerName();
        $action     = $this->dispatcher->getActionName();
        $template   = "$controller/$action";

        // 将 Flash 服务添加到 View
        $view->setVar('flash', $this->flash);

        // 自动渲染视图
        return $view->render($template);
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @param Throwable $e
     * @return mixed|Response
     */
    protected function handleException(Throwable $e)
    {
        // trigger 返回 true/false 表示是否有且执行了事件监听
        $handled = $this->trigger('app.exception', $e);
        // 将处理信息写入 response
        if ($handled) {
            return $this->response;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        throw $e;
    }
}
