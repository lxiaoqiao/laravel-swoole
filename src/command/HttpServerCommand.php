<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/10/24
 * Time: 12:33
 */
namespace Xiaoqiao\LaravelSwoole\command;

use Illuminate\Console\Command;
use Xiaoqiao\LaravelSwoole\server\Manager;

class HttpServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xqserver:http {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    public function start()
    {
        echo 'http服务启动';
        $this->getManager()->start();
    }

    public function getManager()
    {
        return $this->getLaravel()->make('xswoole.manager');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $action = $this->argument('action');

        if (!method_exists($this, $action)) {
            echo '方法不存在';
            return ;
        }
        $this->$action();
    }
}