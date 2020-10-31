<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/10/24
 * Time: 12:31
 */
namespace Xiaoqiao\LaravelSwoole\server;

use Illuminate\Support\Str;
use Xiaoqiao\LaravelSwoole\concerns\WithApplication;
use Xiaoqiao\LaravelSwoole\http\Request as XqRequest;
use Xiaoqiao\LaravelSwoole\http\Response;

class Manager
{
    use WithApplication;
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $laravelApp;

    protected $event = [
        'start',
        'shutDown',
        'request',
    ];

    public function __construct($laravelApp)
    {
        $this->laravelApp = $laravelApp;

        $this->initialize();
    }

    protected function initialize()
    {
        $this->onEvents();
    }

    public function start()
    {
        $this->getServer()->start();
    }

    public function stop()
    {
        $this->getServer()->stop();
    }

    public function onEvents()
    {
        $server = $this->getServer();
        foreach ($this->event as $event) {
            $method = Str::Camel('on_'.$event);
            $callback = method_exists($this, $method) ? [$this, $method] : function () use ($event) {
                $this->laravelApp->make('events')->dispatch("swoole.$event", func_get_args());
            };

            $server->on($event, $callback);
        }
    }

    public function onStart()
    {
        $server = $this->getServer();
        $this->laravelApp->make(PidManager::class)->write($server->master_pid, $server->manager_pid);
    }

    public function onShutDown()
    {
        $this->laravelApp->make(PidManager::class)->delete();
    }

    public function onRequest($swooleRequest, $swooleResponse)
    {
        $laravelRequest = XqRequest::createLaravelRequest($swooleRequest);

        $kernel = $this->laravelApp->make(\Illuminate\Contracts\Http\Kernel::class);
        $laravelResponse = $kernel->handle($laravelRequest);

        Response::make($swooleResponse, $laravelResponse)->send();
    }

    public function getServer()
    {
        return $this->laravelApp->make('xswoole.server');
    }

    public function serConfig($config)
    {
        $this->config = $config;
    }

    public function setServer($server)
    {
        $this->server = $server;
    }
}