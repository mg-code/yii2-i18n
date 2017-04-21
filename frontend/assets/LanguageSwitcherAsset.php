<?php

namespace mgcode\i18n\frontend\assets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * @link https://github.com/mg-code/yii2-i18n
 * @author Maris Graudins <maris@mg-interactive.lv>
 */
class LanguageSwitcherAsset extends AssetBundle
{
    public $sourcePath = '@mgcode/i18n/frontend/assets/files';
    public $js = [
        'js/language-switcher.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

    /**
     * Initializes plugin
     * @param View $view
     * @return $this
     */
    public function initPlugin(View $view, $selector, $options = [])
    {
        $languages = [];
        foreach (\Yii::$app->languageManager->getSupported() as $language) {
            $languages[$language->iso_code] = $language->getUrl(['setLanguage' => $language->iso_code]);
        }
        $options = array_merge([
            'languages' => $languages,
        ], $options);

        $json = Json::encode($options);
        $view->registerJs("$('{$selector}').languageSwitcher({$json});");
        return $this;
    }
}