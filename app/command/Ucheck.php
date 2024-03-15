<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\common\service\OpenService;

class Ucheck extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('ucheck')
            ->setDescription('the ucheck command');
    }

    protected function execute(Input $input, Output $output)
    {
        $open_service = new OpenService();
        $open_service->uCheckFileCommand();
    }
}
