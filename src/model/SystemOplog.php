<?php



declare (strict_types=1);

namespace think\admin\model;

use think\admin\Model;

/**
 * 系统日志模型
 * Class SystemOplog
 * @package think\admin\model
 */
class SystemOplog extends Model
{
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