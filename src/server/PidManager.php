<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/10/24
 * Time: 12:31
 */
namespace Xiaoqiao\LaravelSwoole\server;

class PidManager
{
    protected $pidFile;

    public function __construct($pidFile = null)
    {
        $file = $pidFile ?: sys_get_temp_dir() . '/xswoole.pid';

        $this->setPidFile($file);
    }

    public function write(int $masterPid, int $managerPid)
    {
        if (!is_writable($this->pidFile) && !is_writable(dirname($this->pidFile))) {
            throw new \RuntimeException('文件没有写入权限！');
        }

        file_put_contents($this->pidFile, $masterPid .','. $managerPid);
    }

    public function read()
    {
        $pid = null;

        if (is_readable($this->pidFile)) {
            $content = file_get_contents($this->pidFile);
            $pid = explode(',', $content);
        }

        return [
            'master_pid'  => $pid[0] ?? null,
            'manager_pid' => $pid[1] ?? null,
        ];
    }

    public function delete()
    {
        if (is_writable($this->pidFile)) {
            unlink($this->pidFile);
            return true;
        }

        return false;
    }

    public function setPidFile($pidFile)
    {
        $this->pidFile = $pidFile;
    }
}