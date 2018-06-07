<?php

namespace mgcode\i18n\frontend\widgets;

use yii\base\Widget;

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