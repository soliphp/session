<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Web;

/**
 * 闪存消息
 */
class Flash
{
    protected $cssClasses = [
        'error'   => 'error',
        'notice'  => 'notice',
        'warning' => 'warning',
        'success' => 'success'
    ];

    protected $messages;

    protected $flashKey = '__flashMessages';

    /** @var \Soli\Web\Session */
    protected $session;

    /**
     * Flash constructor.
     *
     * @param Session|null $session
     */
    public function __construct(Session $session = null)
    {
        $this->session = $session;
    }

    /**
     * 设置消息样式
     *
     * @param array $cssClasses
     */
    public function setCssClasses(array $cssClasses)
    {
        $this->cssClasses = array_merge($this->cssClasses, $cssClasses);
    }

    /**
     * @return array
     */
    public function getCssClasses()
    {
        return $this->cssClasses;
    }

    public function error($message)
    {
        $this->message(__FUNCTION__, $message);
    }

    public function notice($message)
    {
        $this->message(__FUNCTION__, $message);
    }

    public function warning($message)
    {
        $this->message(__FUNCTION__, $message);
    }

    public function success($message)
    {
        $this->message(__FUNCTION__, $message);
    }

    /**
     * 处理各个类型的 flash message
     *
     * @param string $type success|error|notice|warning
     * @param string $message
     */
    public function message($type, $message)
    {
        if (isset($this->cssClasses[$type])) {
            $html = '<div class="%s">%s</div>';
            $this->messages[] = sprintf($html, $this->cssClasses[$type], $message);
            $this->session->set($this->flashKey, $this->messages);
        }
    }

    /**
     * 输出 flash messages
     *
     * @param bool $remove 输出后是否删除 flash messages
     */
    public function output($remove = true)
    {
        $remove = (bool)$remove;
        $messages = $this->session->get($this->flashKey, [], $remove);
        if (!empty($messages)) {
            foreach ($messages as $message) {
                echo $message;
            }
        }
        if ($remove) {
            $this->clear();
        }
    }

    /**
     * 清空 flash messages
     * 在同一次请求中，要清除已经设置的 messages 并且要设置新的 messages 时会有用
     */
    public function clear()
    {
        $this->messages = [];
    }
}
