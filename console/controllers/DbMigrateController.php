<?php

namespace console\controllers;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * This is a simple script help you migrating your data from one database to another.
 *
 * Given you have a
 *
 * Class PasswordController
 * @package console\controllers
 */
class DbMigrateController extends Controller
{
    /**
     * @var Query
     */
    private $from;
    /**
     * @var Connection
     */
    private $to;

    /**
     * 初始化
     *
     * @throws \yii\base\Exception
     */
    public function actionIndex()
    {
        $this->from = new Query();
        $this->to = Yii::$app->db2;

        $tables=[
            'order_history',
            'order_option',
            'order_product',
            'order_total',
            'customer_activity',
            'product_image',

//            'attribute',
//            'attribute_description',
//            'attribute_group',
//            'attribute_group_description',
//            'category_description',
//            'category_path',
//            'category_to_store',
//            'country',
//            'coupon_category',
//            'coupon_history',
//            'coupon_product',
//            'currency',
//            'customer_group',
//            'customer_group_description',
//            'customer_history',
//            'customer_login',
//            'customer_transaction',
//            'customer_reward',
//            'express_company',
//            'geo_zone',
//            'length_class',
//            'length_class_description',
//            'manufacturer',
//            'manufacturer_to_store',
//            'option',
//            'option_description',
//            'option_value',
//            'option_value_description',
//            'product_attribute',
//            'product_description',
//            'product_discount',
//            'product_option',
//            'product_related',
//            'product_special',
//            'product_to_category',
//            'product_to_layout',
//            'product_to_store',
//            'setting',
//            'stock_status',
//            'store',
//            'zone',
//            'zone_to_geo_zone',
//            'weight_class',
//            'weight_class_description'
        ];

        Yii::$app->db->createCommand('SET SESSION wait_timeout = 288000;')->execute();
        foreach ($tables as $table) {
            Console::output("开始迁移表格$table");
            $this->migrate($table);
            Console::output("迁移表格{$table}成功");
        }

        $this->complex();

    }

    private function migrate($tablename){
        $this->to->createCommand()->truncateTable("{{%$tablename}}")->execute();
        foreach ($this->from->select('*')->from("{{%$tablename}}")->batch(500) as $rows) {
            Console::output('有'.count($rows).'条记录');
            if (count($rows) > 0) {
                $columns = array_keys($rows[0]);
                $this->to->createCommand()
                    ->batchInsert("{{%$tablename}}", $columns, $rows)
                    ->execute();
            }
        }
    }

    public function complex() {

        $changed = [

            ['product', static function ($input) {
                foreach ($input as &$v) {
                    unset($v['sent'], $v['weidian_product_id'], $v['maxbuy'], $v['gst'], $v['medical'], $v['cost']);
                }
                return $input;
            }],
            ['order', static function ($input) {
                foreach ($input as &$v) {
                    $v['payment_firstname'] = $v['payment_fullname'];
                    $v['shipping_firstname'] = $v['shipping_fullname'];
                    $v['firstname'] = $v['fullname'];
                    $v['payment_address_1'] = $v['payment_address'];
                    $v['shipping_address_1'] = $v['shipping_address'];
                    $v['shipping_company'] = $v['shipping_telephone'];

                    unset($v['fullname'], $v['payment_fullname'],
                        $v['shipping_telephone'],$v['shipping_fullname'],
                        $v['payment_address'], $v['shipping_address'],
                        $v['wx_transaction']);
                }
                return $input;
            }],
            ['customer', static function ($input) {
                foreach ($input as &$v) {
                    $v['firstname'] = $v['fullname'];
                    $v['language_id'] = 4;
                    unset($v['fullname'], $v['omi_openid'], $v['wx_username'],
                        $v['miniid'], $v['approved']);
                }
                return $input;
            }],

            ['address', static function ($input) {
                foreach ($input as &$v) {
                    $v['firstname'] = $v['fullname'];
                    $v['address_1'] = $v['address'];
                    $v['company']= $v['shipping_telephone'];
                    unset($v['fullname'], $v['address'], $v['shipping_telephone']);
                }
                return $input;
            }],

//            ['coupon', static function ($input) {
//                foreach ($input as &$v) {
//                    unset($v['customer_group']);
//                }
//                return $input;
//            }],
//            ['category', static function ($input) {
//                foreach ($input as &$v) {
//                    unset($v['sent'], $v['weidian_category_id']);
//                }
//                return $input;
//            }],
//            ['product_option_value', static function ($input) {
//                foreach ($input as &$v) {
//                    unset($v['weidian_sku_id']);
//                }
//                return $input;
//            }],
        ];

        foreach ($changed as $table) {
            Console::output("开始迁移表格$table[0]");
                $this->complexmigrate($table);
                Console::output("迁移表格{$table[0]}成功");

        }
    }

    private function complexmigrate($table)
    {
        $this->to->createCommand()->truncateTable("{{%$table[0]}}")->execute();

        foreach ($this->from->select('*')->from("{{%$table[0]}}")->batch(500) as $rows) {
            Console::output('有' . count($rows) . '条记录');
            $rows = $table[1]($rows);

            if (count($rows) > 0) {
                $columns = array_keys($rows[0]);
                $this->to->createCommand()
                    ->batchInsert("{{%$table[0]}}", $columns, $rows)
                    ->execute();
            }
        }
    }
}
