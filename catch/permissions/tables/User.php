<?php
namespace catchAdmin\permissions\tables;


use catcher\CatchTable;
use catchAdmin\permissions\tables\forms\Factory;
use catcher\library\table\Actions;
use catcher\library\table\HeaderItem;
use catcher\library\table\Search;

class User extends CatchTable
{
    public function table()
    {
        // TODO: Implement table() method.
        return $this->getTable('user')
                    ->header([
                        HeaderItem::label('')->selection(),
                        HeaderItem::label('账号')->prop('username'),
                        HeaderItem::label('名称')->prop('nickname'),
                        HeaderItem::label('角色')->prop('role_name'),
                        HeaderItem::label('建立者')->prop('creator'),

                        //HeaderItem::label('头像')->prop('avatar')->withPreviewComponent(),

                        //HeaderItem::label('邮箱')->prop('email'),
                        HeaderItem::label('建立时间')->prop('created_at'),
                        HeaderItem::label('状态')->prop('status')->component('status', 'status'),
                        HeaderItem::label('登录IP')->prop('last_login_ip'),
                        HeaderItem::label('最后登录时间')->prop('last_login_time'),
                        HeaderItem::label('操作')->width(200)->actions([
                            Actions::update(), Actions::delete()
                        ])
                    ])
                    ->withSearch([
                        Search::label('账号')->text('username', '账号'),
                        //Search::label('邮箱')->text('email', '邮箱'),
                        Search::label('状态')->status(),
                        //Search::hidden('department_id', '')
                    ])
                    ->withApiRoute('users')
                    ->withActions([
                        Actions::create(),
                        Actions::export()
                    ])
                    ->withExportRoute('user/export')
                    ->withFilterParams([
                        'username' => '',
                        //'email'    => '',
                        'status'   => '',
                        //'department_id' => ''
                    ])
                    ->withDialogWidth('40%')
                    ->selectionChange()
                    ->render();
    }

    protected function form()
    {
        // TODO: Implement form() method.
        return Factory::create('user');
    }
}
