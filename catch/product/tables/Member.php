<?php
namespace catchAdmin\product\tables;


use catcher\CatchTable;
use catchAdmin\product\tables\forms\Factory;
use catcher\library\table\Actions;
use catcher\library\table\HeaderItem;
use catcher\library\table\Search;

class Member extends CatchTable
{
    public function table()
    {
        // TODO: Implement table() method.
        return $this->getTable('member')
                    ->header([
                        HeaderItem::label('')->selection(),
                        HeaderItem::label('账号')->prop('user_account'),
                        HeaderItem::label('名称')->prop('user_name'),
                        HeaderItem::label('联系方式')->prop('user_contact'),
                        HeaderItem::label('余额')->prop('wallet'),
                        HeaderItem::label('APPID')->prop('appid'),
                        HeaderItem::label('APPKEY')->prop('appkey'),
                        HeaderItem::label('新增日期')->prop('created_at'),
                        HeaderItem::label('最后登录时间/IP')->prop('last_login_ip'),
                        HeaderItem::label('状态')->prop('status')->component('status', 'status'),
                        HeaderItem::label('操作')->width(200)->actions([
                            Actions::update(), Actions::delete()
                        ])
                    ])
                    ->withSearch([
                        Search::label('账号')->text('user_account', '输入账号'),
                        Search::label('名称')->text('user_name', '输入名称'),
                        Search::label('状态')->status(),
                    ])
                    ->withApiRoute('member')
                    ->withActions([
                        Actions::create(),
                        //Actions::export()
                    ])
                    //->withExportRoute('user/export')
                    ->withFilterParams([
                        'user_account' => '',
                        'user_name'    => '',
                        'status'   => ''
                    ])
                    ->selectionChange()
                    ->render();
    }

    protected function form()
    {
        // TODO: Implement form() method.
        return Factory::create('member');
    }
}
