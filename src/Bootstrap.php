<?php

namespace dzil\yii2_crud;

use Yii;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app): void
    {
        Yii::setAlias('@dzilcrud', __DIR__);

        if ($app->hasModule('gii')) {
            $gii = $app->getModule('gii');
            if (!isset($gii->generators['dzilcrud'])) {
                $gii->generators['dzilcrud'] = [
                    'class' => 'dzil\yii2_crud\generators\Generator',
                    'templates' => [
                        'default' => '@dzilcrud/generators/template/default',
                        'ajax'    => '@dzilcrud/generators/template/ajax',
                    ],
                ];
            }
        }
    }
}
