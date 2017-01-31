<?php

namespace mgcode\i18n\frontend\widgets;

use mgcode\i18n\common\models\Language;
use yii\base\BootstrapInterface;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\base\Widget;
use yii\web\Application;

/**
 * Renders language dropdown
 * @link https://github.com/mg-code/yii2-language
 * @author Maris Graudins <maris@mg-interactive.lv>
 */
class LanguageSwitcher extends Widget
{
    public function run()
    {
        $languages = \Yii::$app->languageManager->getSupported();
        return $this->render('languageSwitcherView', [
            'languages' => $languages,
        ]);
    }
}