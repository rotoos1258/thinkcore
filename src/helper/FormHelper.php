<?php

// +----------------------------------------------------------------------
// | Library for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2022 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: https://gitee.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 仓库地址 ：https://gitee.com/zoujingli/ThinkLibrary
// | github 仓库地址 ：https://github.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace think\admin\helper;

use think\admin\Helper;
use think\admin\service\SystemService;
use think\admin\model\SystemUserToken;
use think\db\BaseQuery;
use think\Model;

/**
 * 表单视图管理器
 * Class FormHelper
 * @package think\admin\helper
 */
class FormHelper extends Helper
{

    /**
     * 逻辑器初始化
     * @param Model|BaseQuery|string $dbQuery
     * @param string $template 视图模板名称
     * @param string $field 指定数据主键
     * @param mixed $where 限定更新条件
     * @param array $edata 表单扩展数据
     * @return void|array|boolean
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function init($dbQuery, string $template = '', string $field = '', $where = [], array $edata = [])
    {
        $query = $this->buildQuery($dbQuery);
        $field = $field ?: ($query->getPk() ?: 'id');
        $value = $edata[$field] ?? input($field);
        if ($this->app->request->isGet()) {
            if ($value !== null) {
                $exist = $query->where([$field => $value])->where($where)->find();
                if ($exist instanceof Model) $exist = $exist->toArray();
                $edata = array_merge($edata, $exist ?: []);
            }
            if (false !== $this->class->callback('_form_filter', $edata)) {
                $this->class->fetch($template, ['vo' => $edata]);
            } else {
                return $edata;
            }
        }
        if ($this->app->request->isPost()) {
            //修改增加插入租户ID
            $edata = $this->setTenantId(array_merge($this->app->request->post(), $edata));
            //增加发布员，修改员ID写入对应表
            if($this->app->request->post($field)){
                $edata = array_merge($edata,['updated_by' => $this->getUserId()]);
            }else{
                $edata = array_merge($edata,['created_by' => $this->getUserId()]);
            }
            if (false !== $this->class->callback('_form_filter', $edata, $where)) {
                $result = SystemService::instance()->save($query, $edata, $field, $where) !== false;
                if (false !== $this->class->callback('_form_result', $result, $edata)) {
                    if ($result !== false) {
                        $this->class->success(lang('think_library_form_success'));
                    } else {
                        $this->class->error(lang('think_library_form_error'));
                    }
                }
                return $result;
            }
        }
    }

    /**
     * 插入租户id 2022/4/11 by rotoos
     * $param array|string|null $input
     * @retrun array
     */
    private function setTenantId($input):array
    {
        if(!empty($input['tenant_id'])){
            return $input;
        }else{
            $input['tenant_id'] = $this->app->request->header('TenantId');
            return $input;
        }
    }

    /**
     * 根据token取userid 2022/4/11 by rotoos
     */
    private function getUserId()
    {
        $token = $this->app->request->header('Access-Token');
        $type = $this->app->request->header('Api-Name');
        $map = ['type' => $type, 'token' => $token];
        $user = SystemUserToken::mk()->where($map)->where('time','>=',time())->findOrEmpty();
        if(empty($user)){
            return [0, '请重新登录，登录认证无效', 0, 0];
        }else{
            return $user['uuid'];
        }
    }
}