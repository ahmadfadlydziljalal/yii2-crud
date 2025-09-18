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
                        'default'                     => '@dzilcrud/generators/template/default/default',
                        'master-details'              => '@dzilcrud/generators/template/default/master_details',
                        'master-details-details'      => '@dzilcrud/generators/template/default/master_details_details',
                        'ajax-default'                => '@dzilcrud/generators/template/ajax/default',
                        'ajax-master-details'         => '@dzilcrud/generators/template/ajax/master_details',            
                        'ajax-master-details-details' => '@dzilcrud/generators/template/ajax/master_details_details',    
                    ],
                ];
            }
        }
    }
}
