<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/10/24
 * Time: 12:24
 */
namespace Xiaoqiao\LaravelSwoole;

use Illuminate\Support\ServiceProvider;
use Swoole\Http\Server as HttpServer;
use Xiaoqiao\LaravelSwoole\command\HttpServerCommand;
use Xiaoqiao\LaravelSwoole\server\Manager;
use Xiaoqiao\LaravelSwoole\server\PidManager;

class LaravelSwooleProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommand();
        $this->registerManager();
        $this->registerPidManager();
        $this->registerService();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishFiles();
        $this->mergeConfig();
    }

    protected function publishFiles()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/config/swoole.php' => config_path('xswoole.php')], 'xswoole');
        }
    }

    protected function mergeConfig()
    {
        $source = __DIR__ . '/config/swoole.php';

        $this->mergeConfigFrom($source, 'xswoole');
    }

    protected function registerCommand()
    {
        $this->commands(HttpServerCommand::class);
    }

    protected function registerManager()
    {
        $this->app->singleton(Manager::class, function($laravelApp){
            return new Manager($laravelApp);
        });

        $this->app->alias(Manager::class, 'xswoole.manager');
    }

    protected function registerPidManager()
    {
        $this->app->singleton(PidManager::class, function(){
            return new PidManager();
        });
    }

    protected function registerService()
    {
        $this->app->singleton('xswoole.server', function(){
            $app = $this->createSwooleService();

            return $app;
        });
    }

    protected function createSwooleService()
    {
        $config = config('xswoole.server');
        $ip = $config['ip'];
        $port = $config['port'];
        $socketType = $config['socket_type'];
        $processType = $config['process_type'];

        $app = new HttpServer($ip, $port, $processType, $socketType);

        return $app;
    }
}