<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Web;

/**
 * 控制器
 *
 * @property \Soli\Dispatcher $dispatcher
 * @property \Soli\Web\Request $request
 * @property \Soli\Web\Response $response
 * @property \Soli\Web\Session $session
 * @property \Soli\Web\Flash $flash
 * @property \Soli\ViewInterface $view
 * @codeCoverageIgnore
 */
class Controller extends \Soli\Controller
{
    /**
     * 手工渲染视图
     *
     * @param string $template 模板路径
     * @param array $vars 模板变量
     * @return Response
     */
    protected function render(string $template, array $vars = [])
    {
        $vars['flash'] = $this->flash;
        $this->view->setVars($vars);

        $content = $this->view->render($template);
        $this->response->setContent($content);

        return $this->response;
    }

    /**
     * 转发给另一个控制器
     *
     * @param string $controller
     * @param array $action
     * @param array $params
     */
    protected function forward(string $controller, array $action = [], array $params = [])
    {
        return $this->dispatcher->forward([
            'handler' => $controller,
            'action' => $action,
            'params' => $params,
        ]);
    }

    /**
     * 重定向到某个 URL
     */
    protected function redirect($url, $code = 302)
    {
        return new RedirectResponse($url, $code);
    }

    protected function json($data, int $statusCode = 200, int $jsonOptions = 0)
    {
        if (!is_json($data)) {
            $data = json_encode($data, $jsonOptions);
        }
        $this->response->setStatusCode($statusCode);
        $this->response->setContentType("application/json", "UTF-8");
        $this->response->setContent($data);
        return $this->response;
    }

    /**
     * @param string $filePath 文件路径
     * @param string $attachmentName 附件名
     * @return BinaryFileResponse
     */
    protected function file($filePath, string $attachmentName = null)
    {
        if ($attachmentName === null) {
            $basePath = basename($filePath);
        } else {
            $basePath = $attachmentName;
        }

        $response = new BinaryFileResponse($filePath);
        $response->setHeader("Content-Description: File Transfer");
        $response->setHeader("Content-Type: application/octet-stream");
        $response->setHeader("Content-Disposition: attachment; filename=$basePath;");
        $response->setHeader("Content-Transfer-Encoding: binary");

        return $response;
    }
}
