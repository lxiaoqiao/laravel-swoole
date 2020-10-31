<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/10/27
 * Time: 15:35
 */
namespace Xiaoqiao\LaravelSwoole\http;

use Swoole\Http\Request as SwooleRequest;
use Illuminate\Http\Request as LaravelRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

class Request
{
    protected static $laravelRequest;

    public static function createLaravelRequest(SwooleRequest $swooleRequest)
    {
        $request = new SymfonyRequest(...self::parseRequestParameters($swooleRequest));

        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && \in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new ParameterBag($data);
        }

        self::$laravelRequest = LaravelRequest::createFromBase($request);

        return self::$laravelRequest;
    }

    protected static function parseRequestParameters(SwooleRequest $swooleRequest)
    {
        $get = $swooleRequest->get ?? [];
        $post = $swooleRequest->post ?? [];
        $header = $request->header ?? [];
        $server = $swooleRequest->server ?? [];
        $server = static::transformServerParameters($server, $header);
        $cookies = $swooleRequest->cookie ?? [];
        $files = $swooleRequest->files ?? [];
        $content = $swooleRequest->rawContent();

        return [$get, $post, [], $cookies, $files, $server, $content];
    }

    /**
     * Transforms $_SERVER array.
     *
     * @param array $server
     * @param array $header
     *
     * @return array
     */
    protected static function transformServerParameters(array $server, array $header)
    {
        $__SERVER = [];

        foreach ($server as $key => $value) {
            $key = strtoupper($key);
            $__SERVER[$key] = $value;
        }

        foreach ($header as $key => $value) {
            $key = str_replace('-', '_', $key);
            $key = strtoupper($key);

            if (! in_array($key, ['REMOTE_ADDR', 'SERVER_PORT', 'HTTPS'])) {
                $key = 'HTTP_' . $key;
            }

            $__SERVER[$key] = $value;
        }

        return $__SERVER;
    }
}
