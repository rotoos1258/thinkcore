<?php

declare (strict_types=1);

namespace think\admin\service;


use think\admin\model\SystemUserToken;
use think\admin\model\SysUserRole;
use think\admin\model\SysRoleDept;
use think\admin\model\SystemUser;
use think\admin\model\SysDept;
use think\admin\model\SysRole;
use think\admin\Service;

/**
 * 数据权限服务
 * Class DataScopeService
 * @package app\admin\service
 */
class DataScopeService extends Service
{

    /**
     * 获取用户ID集合
     * @return array
     */
    public function setDataScope(): array
    {
        $token = $this->app->request->header('Access-Token');
        $type = $this->app->request->header('Api-Name');
        $map = ['type' => $type, 'token' => $token];
        $user = SystemUserToken::mk()->where($map)->where('time','>=',time())->findOrEmpty();
        if(empty($user)){
            return [0, '请重新登录，登录认证无效', 0, 0];
        }else{
            $userid = $user['uuid'];
        }
        $role_ids = SysUserRole::mk()->where(['user_id' => $userid])->column('role_id');
        $roles = SysRole::mk()->whereIn('id',$role_ids)->select()->toArray();
        p($role_ids);
        return $this->getDeptUserIdsBy($userid, $roles, $role_ids);
        //$dept_ids = SystemRoleDept::mk()->whereIn('role_id', $role_ids)->column('dept_id');
    }

    /**
     * 数据范围查询
     * @param $userid
     * @param $role_ids
     * @return mixed
     */
    public function dataRange(int $userid, $roles = [], $role_ids = [])
    {
        if (AdminService::instance()->isSuper()) {
            return $this;
        }

        $userIds =  $this->getDeptUserIdsBy($userid, $roles, $role_ids);

        if (empty($userIds)) {
            return $this;
        }
    }

    /**
     * 获取部门IDs
     *
     * @param $roles
     * @return array
     */
    public function getDeptUserIdsBy(int $userid, $roles = [], $role_ids = [])
    {
        $userIds = [];
        $isAll = false;
        $user = SystemUser::mk()->where(['id' => $userid,'is_deleted' => 0])->findOrEmpty();
        if (empty($roles)) {
            return [0, '账号未分配角色，请让管理员分配相应角色再操作。', 0, 0];
        }
        foreach ($roles as $role) {
            switch ($role['data_scope']) {
                case 0:
                    //全部
                    $isAll = true;
                    break;
                case 1:
                    //自定义
                    $dept_ids = SysRoleDept::mk()->whereIn('role_id', $role_ids)->column('dept_id');
                    $userIds = array_merge($userIds, $this->getUserIdsByDeptId($dept_ids));
                    break;
                case 2:
                    //本部门
                    $userIds[] = array_merge([$user['id']], $this->getUserIdsByDeptId([$user['dept_id']]));
                    break;
                case 3:
                    // 本部门及以下
                    $dept_ids = SysDept::mk()->where(['parent_id'=> $user['dept_id']])->column('id');
                    array_unshift($dept_ids,$user['dept_id']);
                    $userIds = $this->getUserIdsByDeptId($dept_ids);
                    break;
                case 4:
                    //本人
                    $userIds[] = $user['id'];
                    break;
                default:
                    break;
            }

            // 如果有全部数据 直接跳出
            if ($isAll) {
                break;
            }
        }
        p($userIds);
        return $userIds;
    }

    /**
     * 获取UserID
     * @param $id
     * @return array
     */
    protected function getUserIdsByDeptId(array $id)
    {
        return SystemUser::mk()->whereIn('dept_id', $id)->column('id');
    }
}