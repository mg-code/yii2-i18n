<?php

namespace mgcode\i18n\common\components;

use mgcode\i18n\common\models\Language;
use yii\base\BootstrapInterface;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\web\Application;

/**
 * LanguageManager sets language based on HTTP GET parameter.
 * Simply add UrlManager config under `components`
 * as it is shown in the following example:
 * ```php
 * 'components' => [
 *     'languageManager' => [
 *         'class' => \mgcode\i18n\common\components\LanguageManager::className(),
 *         'defaultLanguage' => 'en',
 *     ],
 *     .......
 * ],
 * ```
 * Then add bootstrap configuration under `bootstrap`
 * as it is shown in the following example:
 * ```php
 * 'bootstrap' => [
 *     'languageManager',
 *     ......
 * ],
 * ```
 * @link https://github.com/mg-code/yii2-language
 * @author Maris Graudins <maris@mg-interactive.lv>
 */
class LanguageManager extends Object implements BootstrapInterface
{
    /** @var string Iso code of default language */
    public $defaultLanguage;

    /** @var bool Automatically load rules to urlManager */
    public $loadUrlRules = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->defaultLanguage === null) {
            throw new InvalidParamException('`defaultLanguage` must be set');
        }
        if (!in_array($this->defaultLanguage, $this->getSupported(true))) {
            throw new InvalidParamException('`defaultLanguage` is not supported. Did you insert supported languages into language table?');
        }
        if ($this->loadUrlRules) {
            $this->loadUrlRules();
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_ACTION, function () use ($app) {
            $request = $app->getRequest();
            $language = (string) $request->get('lang');
            if ($language && in_array($language, $this->getSupported(true))) {
                $app->language = $language;
            } else {
                $app->language = $this->defaultLanguage;
            }
            $app->formatter->locale = $app->language;
        });
    }

    private $_supported;

    /**
     * Returns list of supported languages.
     * @param bool $returnIsoCode
     * @return array|Language[]
     */
    public function getSupported($returnIsoCode = false)
    {
        if ($this->_supported === null) {
            $this->_supported = Language::find()->sort()->all();
        }
        if ($returnIsoCode) {
            return ArrayHelper::getColumn($this->_supported, 'iso_code');
        }
        return $this->_supported;
    }

    /**
     * Loads url rules for multi-language pages
     */
    protected function loadUrlRules()
    {
        $urlManager = \Yii::$app->urlManager;

        $defaultRoute = \Yii::$app->defaultRoute;
        if (!$defaultRoute || $defaultRoute == 'site') {
            $defaultRoute = 'site/index';
        }

        // Add url rules for all other languages
        $otherLanguages = $this->getSupported(true);
        unset($otherLanguages[array_search($this->defaultLanguage, $otherLanguages)]);
        if ($otherLanguages) {
            $implode = implode('|', $otherLanguages);
            $urlManager->addRules([
                [
                    'pattern' => '/<lang:('.$implode.')>/',
                    'route' => $defaultRoute,
                ],
                [
                    'pattern' => '/<lang:('.$implode.')>/<module:>/<controller:>',
                    'route' => '<module>/<controller>',
                ],
                [
                    'pattern' => '/<lang:('.$implode.')>/<module:>/<controller:>/<action:>',
                    'route' => '<module>/<controller>/<action>',
                ],
                [
                    'pattern' => '/<lang:('.$implode.')>/<controller:>',
                    'route' => '<controller>',
                ],
                [
                    'pattern' => '/<lang:('.$implode.')>/<controller:>/<action:>',
                    'route' => '<controller>/<action>',
                ],
            ]);
        }

        // Add url rules for default language
        $urlManager->addRules([
            [
                'pattern' => '/',
                'route' => $defaultRoute,
                'defaults' => ['lang' => $this->defaultLanguage],
            ],
            [
                'pattern' => '/<module:>/<controller:>',
                'route' => '<module>/<controller>',
                'defaults' => ['lang' => $this->defaultLanguage],
            ],
            [
                'pattern' => '/<module:>/<controller:>/<action:>',
                'route' => '<module>/<controller>/<action>',
                'defaults' => ['lang' => $this->defaultLanguage],
            ],
            [
                'pattern' => '/<controller:>',
                'route' => '<controller>',
                'defaults' => ['lang' => $this->defaultLanguage],
            ],
            [
                'pattern' => '/<controller:>/<action:>',
                'route' => '<controller>/<action>',
                'defaults' => ['lang' => $this->defaultLanguage],
            ],
        ]);
    }
}