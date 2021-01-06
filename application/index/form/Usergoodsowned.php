<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2019 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\index\form;

use think\Db;

/**
 * 用户已购商品管理动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-30
 * @desc    description
 */
class UserGoodsOwned
{
    // 基础条件
    public $condition_base = [];

    /**
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function __construct($params = [])
    {
        // 用户信息
        if(!empty($params['system_user']))
        {
            $this->condition_base[] = ['ug.user_id', '=', $params['system_user']['id']];
        }
    }

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Run($params = [])
    {
        return [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'is_search'     => 1,
                'search_url'    => MyUrl('index/usergoodsowned/index'),
                'is_delete'     => 0,
                'delete_url'    => MyUrl('index/usergoodsowned/delete'),
                'delete_key'    => 'ids',
            ],
            // 表单配置
            'form' => [
                [
                    'label'         => '商品信息',
                    'view_type'     => 'module',
                    'view_key'      => 'usergoodsowned/module/goods',
                    'grid_size'     => 'lg',
                    'is_sort'       => 1,
                    'sort_field'    => 'g.title',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'g.title|g.model|g.simple_desc|g.seo_title|g.seo_keywords|g.seo_keywords',
                        'where_type'        => 'like',
                        'placeholder'       => '请输入商品名称/简述/SEO信息'
                    ],
                ],
                [
                    'label'         => '购买价格(元)',
                    'view_type'     => 'field',
                    'view_key'      => 'price',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'ug.price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '购买时间',
                    'view_type'     => 'field',
                    'view_key'      => 'upd_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'ug.upd_time',
                    ],
                ],
            ],
        ];
    }
}
?>