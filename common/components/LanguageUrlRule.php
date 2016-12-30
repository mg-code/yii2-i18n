<?php

namespace mgcode\language\common\components;

use yii\web\UrlRule;

/**
 * LangUrlRule automatically adds `lang` parameter to parameters list.
 *
 * @link https://github.com/mg-code/yii2-language
 * @author Maris Graudins <maris@mg-interactive.lv>
 */
class LanguageUrlRule extends UrlRule
{
    /** @inheritdoc */
    public function createUrl($manager, $route, $params)
    {
        if(!isset($params['lang'])) {
            $params['lang'] = \Yii::$app->language;
        }
        return parent::createUrl($manager, $route, $params);
    }
}