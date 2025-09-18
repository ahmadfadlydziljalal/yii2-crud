<?php

namespace dzil\yii2_crud;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * @author Ahmad Fadly Dzil Jalal <ahmadfadlydziljalal@gmail.com>
 * @since 1.0
 */
class Bootstrap implements BootstrapInterface {

    /**
     * Bootstrap method to be called during the application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app): void
    {
       Yii::setAlias("@dzilcrud", __DIR__);
        if ($app->hasModule('gii')) {
            if (!isset($app->getModule('gii')->generators['dzilcrud'])) {
                $app->getModule('gii')->generators['dzilcrud'] = 'dzil\yii2_dzil_crud\generators\Generator';
            }
        }
    }

}
