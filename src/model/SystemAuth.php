<?php



declare (strict_types=1);

namespace think\admin\model;

use think\admin\Model;

/**
 * 用户权限模型
 * Class SystemAuth
 * @package think\admin\model
 */
class SystemAuth extends Model
{
    /**
     * 日志名称
     * @var string
     */
    protected $oplogName = '系统权限';

    /**
     * 日志类型
     * @var string
     */
    protected $oplogType = '系统权限管理';

    /**
     * 获取权限数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function items(): array
    {
        return static::mk()->where(['status' => 1])->order('sort desc,id desc')->select()->toArray();
    }

    /**
     * 删除权限事件
     * @param string $ids
     */
    public function onAdminDelete(string $ids)
    {
        if (count($aids = str2arr($ids ?? '')) > 0) {
            SystemNode::mk()->whereIn('auth', $aids)->delete();
        }
        sysoplog($this->oplogType, "删除{$this->oplogName}[{$ids}]及授权配置");
    }

    /**
     * 格式化创建时间
     * @param string $value
     * @return string
     */
    public function getCreateAtAttr(string $value): string
    {
        return format_datetime($value);
    }
}