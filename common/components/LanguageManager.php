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
    const PARAM_UUID_LANGUAGE = 'lang';

    /** @var string Iso code of default language */
    public $defaultLanguage;

    /** @var bool Automatically load rules to urlManager */
    public $loadUrlRules = true;

    /** @var bool Use language code in urls for default language */
    public $defaultLanguageCodeInUrl = false;

    private $_supported;

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
        $app->on(Application::EVENT_BEFORE_ACTION, function () {
            $this->detectLanguage();
        });
    }

    /**
     * Returns list of supported languages.
     * @param bool $returnIsoCode
     * @return array|Language[]
     */
    public function getSupported($returnIsoCode = false)
    {
        if ($this->_supported === null) {
            $this->_supported = Language::find()->sort()->active()->all();
        }
        if ($returnIsoCode) {
            return ArrayHelper::getColumn($this->_supported, 'iso_code');
        }
        return $this->_supported;
    }

    public function redirectToLanguage($language)
    {
        $urlRoute = $this->buildUrlRoute($language);
        \Yii::$app->response->redirect($urlRoute);
        \Yii::$app->end();
    }

    /**
     * Whether to use uuid parameter for first page redirect.
     * Most search engine crawlers are excluded.
     * @return bool
     */
    public function useUuidParameter()
    {
        return !(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT']));
    }

    protected function buildUrlRoute($language)
    {
        $urlRoute = array_merge(
            ['/'.\Yii::$app->controller->route],
            \Yii::$app->request->get(),
            ['lang' => $language]
        );
        unset($urlRoute['setLanguage']);
        return $urlRoute;
    }

    /**
     * Detects user language
     */
    protected function detectLanguage()
    {
        $request = \Yii::$app->getRequest();

        // Changes language
        if (!$request->getIsAjax() && ($setLanguage = $request->get('setLanguage'))) {
            $this->changeLanguage($setLanguage);
        }

        // Check language in _GET
        $language = (string) $request->get('lang');
        // See for headers
        if (!$language) {
            $language = (string) $request->getHeaders()->get('Set-Language');
        }
        // See in body payload
        if (!$language) {
            $language = (string) $request->getBodyParam('lang');
        }

        if ($language && in_array($language, $this->getSupported(true))) {
            \Yii::$app->language = $language;
        } else {
            \Yii::$app->language = $this->defaultLanguage;
        }
        \Yii::$app->formatter->locale = \Yii::$app->language;
        $this->handleLandingPage();
    }

    /**
     * Handles landing page. Redirects to selected language.
     */
    protected function handleLandingPage()
    {
        $request = \Yii::$app->getRequest();
        if (!\Yii::$app->has('uuid') || !$this->useUuidParameter() || $request->url != '/') {
            return;
        }

        $savedLanguage = \Yii::$app->uuid->getParam(static::PARAM_UUID_LANGUAGE);
        if ($savedLanguage && in_array($savedLanguage, $this->getSupported(true)) && $savedLanguage != \Yii::$app->language) {
            $this->redirectToLanguage($savedLanguage);
        }
    }

    /**
     * @param $language
     */
    protected function changeLanguage($language)
    {
        if (!in_array($language, $this->getSupported(true))) {
            return;
        }
        if ($this->useUuidParameter() && \Yii::$app->has('uuid')) {
            \Yii::$app->uuid->setParam(static::PARAM_UUID_LANGUAGE, $language);
        }
        $this->redirectToLanguage($language);
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
        $languages = $this->getSupported(true);
        if (!$this->defaultLanguageCodeInUrl) {
            unset($languages[array_search($this->defaultLanguage, $languages)]);
        }
        if ($languages) {
            $implode = implode('|', $languages);
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
        if (!$this->defaultLanguageCodeInUrl) {
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
}