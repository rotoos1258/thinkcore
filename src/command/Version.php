<?php


declare (strict_types=1);

namespace think\admin\command;

use think\admin\Command;
use think\console\Input;
use think\console\Output;

/**
 * 框架版本号指令
 * Class Version
 * @package think\admin\command
 */
class Version extends Command
{
    /**
     * 指令任务配置
     */
    protected function configure()
    {
        $this->setName('xadmin:version');
        $this->setDescription("ThinkLibrary and ThinkPHP Version for ThinkAdmin");
    }

    /**
     * 任务执行入口
     * @param Input $input
     * @param Output $output
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        $output->writeln("ThinkPHPCore {$this->app->version()}");
        $output->writeln("ThinkLibrary {$this->process->version()}");
    }
}