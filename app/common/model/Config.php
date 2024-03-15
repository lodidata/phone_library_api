<?php


namespace app\common\model;


class Config extends Base
{
    public function getConfig(string $component)
    {
        $data    = [];
        $configs = $this->where('pid', $this->where('component', $component)->value('id'))
            ->field('id,`key` as k,value,pid')
            ->select();

        foreach ($configs as $config) {
            if (strpos($config['k'], '.') !== false) {
                list($object, $key)  = explode('.', $config['k']);
                $data[$object][$key] = $config['value'];
            }
        }

        return $data;
    }
}