<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/10/27
 * Time: 15:35
 */
namespace Xiaoqiao\LaravelSwoole\http;

class Response
{
    /**
     * @var \Swoole\Http\Response
     */
    protected $swooleResponse;

    /**
     * @var \Illuminate\Http\Response
     */
    protected $laravelResponse;

    public static function make($swooleResponse, $laravelResponse)
    {
        return new static($swooleResponse, $laravelResponse);
    }

    public function __construct($swooleResponse, $laravelResponse)
    {
        $this->swooleResponse = $swooleResponse;
        $this->laravelResponse = $laravelResponse;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    /**
     * Sends HTTP headers.
     *
     * @return $this
     */
    public function sendHeaders()
    {
        $laravelResponse = $this->laravelResponse;

        // headers
        foreach ($laravelResponse->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            foreach ($values as $value) {
                $this->swooleResponse->header($name, $value);
            }
        }

        // cookies
        foreach ($laravelResponse->headers->getCookies() as $cookie) {
            $this->swooleResponse->header('Set-Cookie', $cookie);
        }

        // status
        $this->swooleResponse->status($laravelResponse->getStatusCode());
    }

    /**
     * Sends content for the current web response.
     *
     * @return $this
     */
    public function sendContent()
    {
        $laravelResponse = $this->laravelResponse;

        $this->swooleResponse->end($laravelResponse->getContent());
    }
}