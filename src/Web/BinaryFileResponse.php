<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Web;

/**
 * 文件响应
 *
 * @codeCoverageIgnore
 */
class BinaryFileResponse extends Response
{
    /**
     * 文件路径
     *
     * @var string
     */
    protected $file;

    /**
     * BinaryFileResponse constructor.
     *
     * @param string $file 文件路径
     * @param int $code 状态码，默认 302 临时重定向
     * @param string $message 状态描述
     */
    public function __construct(string $file, int $code = 200, string $message = null)
    {
        parent::__construct('', $code, $message);

        $this->file = $file;

        $this->setStatusCode($code, $message);
    }

    public function sendContent()
    {
        $out = fopen('php://output', 'wb');
        $file = fopen($this->file, 'rb');

        // 可通过 maxlength, offset 和相关的 header 做断点续传
        stream_copy_to_stream($file, $out);

        fclose($out);
        fclose($file);

        return $this;
    }
}