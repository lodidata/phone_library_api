<?php
namespace app\common\service;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;


class ExportService
{
    public function csv ($params) {
        $title       = $params['title'];
        $member_id   = isset($params['member_id']) ? $params['member_id'] : 0;
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $heard = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE',
            'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR',
            'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
        ];

        $i=0;
        if($title){
            // 标题
            foreach ($title as $value) {
                $sheet->setCellValue($heard[$i] . '1', $value);
                $i++;
            }
            // 标题end
        }

        $writer   = new Csv($spreadsheet);
        $fileName = $params['filename'];
        $fileType = 'txt';

        // 设置输出头部信息
        header('Content-Encoding: UTF-8');
        header("Content-Type: text/csv; charset=UTF-8");
        header('Content-Description: File Transfer');
        header("Expires: 0");
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        // 输出Excel07版本
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // 输出名称
        header('Content-Disposition: attachment;filename=' . $fileName . '.' . $fileType);
        //禁止缓存
        header('Cache-Control: max-age=0');
        $root_path = \config('filesystem.disks.local.root');
        $path      =  $root_path. DIRECTORY_SEPARATOR .'export'.DIRECTORY_SEPARATOR.$member_id.DIRECTORY_SEPARATOR.date('Ymd');
        !is_dir($path) && mkdir($path, 0777, true);
        $file_name = $path.DIRECTORY_SEPARATOR.$fileName. '.' .$fileType;
        $writer->setUseBOM(true);  // csv 中文乱码问题
        $writer->save($file_name);
        // 清除数据
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        // 清除数据end
        $fp = fopen($file_name, 'a');//打开output流

        $num = 0;
        foreach ($params['data'] as $v) {
            fputcsv($fp, $v);
            $num ++;
            if($num > 2000){
                $num = 0;
                //刷新输出缓冲到浏览器
                ob_flush();
                flush();//必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
            }
        }

        fclose($fp); // 关闭文件
        unset($data);
        $url = \config('filesystem.disks.local.img_domain').str_replace($root_path,'',$file_name);
        return [$url];
        //exit(); // 防止调试模式中输出html代码
    }

}