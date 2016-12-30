<?php

namespace mgcode\language\common\components;

use mgcode\language\common\models\Language;
use yii\base\BootstrapInterface;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\web\Application;

/**
 * LanguageManager sets language based on HTTP GET parameter.
 * Simply add UrlManager config under `components`
 * as it is shown in the following example:
 * ```php
 * 'components' => [
 *     'languageManager' => [
 *         'class' => \mgcode\language\common\components\LanguageManager::className(),
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
        if (!in_array($this->defaultLanguage, $this->getSupported())) {
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
            if ($language && in_array($language, $this->getSupported())) {
                $app->language = $language;
            } else {
                $app->language = $this->defaultLanguage;
            }
        });
    }

    /**
     * Loads url rules for multi-language pages
     */
    protected function loadUrlRules()
    {
        $urlManager = \Yii::$app->urlManager;

        // Add url rules for all other languages
        $otherLanguages = $this->getSupported();
        unset($otherLanguages[array_search($this->defaultLanguage, $this->getSupported())]);
        if ($otherLanguages) {
            $implode = implode('|', $otherLanguages);
            $urlManager->addRules([
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

    private $_supported;

    /**
     * Returns list of supported languages.
     * @return array
     */
    protected function getSupported()
    {
        if ($this->_supported !== null) {
            return $this->_supported;
        }
        $this->_supported = Language::find()->select('iso_code')->column();
        return $this->_supported;
    }
}