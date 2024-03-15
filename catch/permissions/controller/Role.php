<?php
namespace catchAdmin\permissions\controller;

use catchAdmin\permissions\model\Roles;
use catcher\base\CatchRequest as Request;
use catcher\base\CatchController;
use catcher\CatchResponse;
use catcher\exceptions\FailedException;
use think\response\Json;
use catchAdmin\permissions\model\Roles as RoleModel;
use think\facade\Db;

class Role extends CatchController
{
    protected $role;

    public function __construct(RoleModel $role)
    {
        $this->role = $role;
    }

  /**
   *
   * @time 2019年12月09日
   * @return string|Json
   */
    public function index()
    {
        $res = $this->role->getList();
        if($res instanceof \think\Paginator){
            return CatchResponse::paginate($res);
        }
        return CatchResponse::success($res);
    }

    /**
     *
     * @time 2019年12月11日
     * @param Request $request
     * @return Json
     * @throws \think\db\exception\DbException
     */
    public function save(Request $request)
    {
        $params = $request->param();

        if (Roles::where('identify', $params['identify'])->find()) {
            return CatchResponse::fail('角色标识 [' . $params['identify'] . ']已存在');
        }
        $params['parent_id'] = 0;
        $params['data_range'] = 1;

        $this->role->storeBy($params);
        // 分配权限
        if (count($params['permissions'])) {
            $this->role->attachPermissions(array_unique($params['permissions']));
        }
        // 分配部门
        if (isset($params['departments']) && count($params['departments'])) {
            $this->role->attachDepartments($params['departments']);
        }
        // 添加角色
        return CatchResponse::success();
    }

    public function read($id)
    {
      $role = $this->role->findBy($id);
      $role->permissions = $role->getPermissions();
      $role->departments = $role->getDepartments();
      return CatchResponse::success($role);
    }

    /**
     *
     * @time 2019年12月11日
     * @param $id
     * @param Request $request
     * @return Json
     * @throws \think\db\exception\DbException
     */
    public function update($id, Request $request): Json
    {
        if (Roles::where('identify', $request->param('identify'))->where('id', '<>', $id)->find()) {
            return CatchResponse::fail('角色标识 [' . $request->param('identify') . ']已存在');
        }

        $this->role->updateBy($id, $request->param());
        $role = $this->role->findBy($id);

        $hasPermissionIds = $role->getPermissions()->column('id');

        $permissionIds = $request->param('permissions');

        // 已存在权限 IDS
        $existedPermissionIds = [];
        foreach ($hasPermissionIds as $hasPermissionId) {
            if (in_array($hasPermissionId, $permissionIds)) {
                $existedPermissionIds[] = $hasPermissionId;
            }
        }

        $attachIds = array_diff($permissionIds, $existedPermissionIds);
        $detachIds = array_diff($hasPermissionIds, $existedPermissionIds);

        if (!empty($detachIds)) {
            $role->detachPermissions($detachIds);
        }
        if (!empty($attachIds)) {
            $role->attachPermissions(array_unique($attachIds));
        }

        // 更新department
        $hasDepartmentIds = $role->getDepartments()->column('id');
        $departmentIds = $request->param('departments',[]);

        // 已存在部门 IDS
        $existedDepartmentIds = [];
        foreach ($hasDepartmentIds as $hasDepartmentId) {
            if (in_array($hasDepartmentId, $departmentIds)) {
                $existedDepartmentIds[] = $hasDepartmentId;
            }
        }

        $attachDepartmentIds = array_diff($departmentIds, $existedDepartmentIds);
        $detachDepartmentIds = array_diff($hasDepartmentIds, $existedDepartmentIds);

        if (!empty($detachDepartmentIds)) {
            $role->detachDepartments($detachDepartmentIds);
        }
        if (!empty($attachDepartmentIds)) {
            $role->attachDepartments(array_unique($attachDepartmentIds));
        }

        return CatchResponse::success();
    }

    /**
     *
     * @time 2019年12月11日
     * @param $id
     * @throws FailedException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @return Json
     */
    public function delete($id): Json
    {
        if($id == 1){
            return CatchResponse::fail('禁止操作');
        }
        if ($this->role->where('parent_id', $id)->find()) {
            return CatchResponse::fail('存在子角色，无法删除');
        }
        $role = $this->role->findBy($id);
        $userCount = Db::name('user_has_roles')->where('role_id',$id)->count();
        if($userCount){
            return CatchResponse::fail('请先移除用户再删除');
        }
        // 删除权限
        $role->detachPermissions();
        // 删除部门关联
        $role->detachDepartments();
        // 删除用户关联
        $role->users()->detach();
        // 删除
        $this->role->deleteBy($id);

        return CatchResponse::success();
    }
}
