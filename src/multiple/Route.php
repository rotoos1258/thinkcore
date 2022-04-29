<?php



declare (strict_types=1);

namespace think\admin\multiple;

use think\Route as ThinkRoute;

/**
 * 自定义路由对象
 * Class Route
 * @package think\admin\multiple
 */
class Route extends ThinkRoute
{
    /**
     * 重新应用配置
     * @return $this
     */
    public function reload(): Route
    {
        $this->config = array_merge($this->config, $this->app->config->get('route'));
        return $this;
    }
}