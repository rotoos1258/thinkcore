<?php



declare (strict_types=1);

namespace think\admin\model;

use think\admin\Model;

/**
 * 授权节点模型
 * Class SystemNode
 * @package think\admin\model
 */
class SystemNode extends Model
{
    /**
     * 绑定模型名称
     * @var string
     */
    protected $name = 'SystemAuthNode';

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