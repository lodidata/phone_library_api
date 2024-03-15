<?php
namespace catchAdmin\permissions\tables\forms;

use catchAdmin\permissions\model\Department as DepartmentModel;
use catchAdmin\permissions\model\Job;
use catchAdmin\permissions\model\Roles;
use catcher\library\form\Form;

class User extends Form
{
    public function fields(): array
    {
        // TODO: Implement fields() method.
        return [
            self::input('username', '账号')->clearable(true)->required(),
            self::input('password', '密码')
                ->placeholder('请输入密码')->clearable(true)->required(),
            self::input('repassword', '确认密码')
                ->placeholder('请输入确认密码')->clearable(true)->required(),
            self::input('nickname', '昵称')->clearable(true),

            /*self::cascader('department_id', '部门', [])
                ->col(self::col(12))
                ->options(
                    DepartmentModel::field(['id', 'parent_id', 'department_name'])->select()->toTree()
                )
                ->props(self::props('department_name', 'id', [
                    'checkStrictly' => true
                ]))->clearable(true),*/

            //self::email('email', '邮箱')->required()->clearable(true),

            /*self::selectMultiple('jobs', '岗位', [])
                ->col(self::col(12))->options(
                    Job::where('status', Job::ENABLE)->field(['id as value', 'job_name as label'])->select()->toArray()
                )->clearable(true)->filterable(true),*/



            self::tree('roles', '角色', [])
                ->props(self::props('role_name', 'id', [], Roles::field(['id', 'parent_id', 'role_name'])->select()->toTree()))
                ->required(),
            self::radio('status', '状态', 1)->options(
                self::options()->add('启用', 1)->add('禁用', 2)->render()
            ),
        ];
    }

}
