<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Web;

/**
 * 重定向响应
 *
 * @codeCoverageIgnore
 */
class RedirectResponse extends Response
{
    /**
     * 跳转地址
     *
     * @var string
     */
    protected $targetUrl;

    /**
     * Response constructor.
     *
     * @param string $url 跳转地址
     * @param int $code 状态码，默认 302 临时重定向
     * @param string $message 状态描述
     */
    public function __construct(string $url, int $code = 302, string $message = null)
    {
        parent::__construct('', $code, $message);

        $this->setTargetUrl($url);

        if ($code < 300 || $code > 308) {
            $code = 302;
        }
        $this->setStatusCode($code, $message);
    }

    public function setTargetUrl(string $url)
    {
        $this->targetUrl = $url;

        $this->setContent(
            sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url=%1$s" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));

        $this->setHeader('Location', $url);

        return $this;
    }
}