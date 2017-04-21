<?php

namespace mgcode\i18n\common\behaviors;

use yii\base\Behavior;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Class I18nAttributeBehavior
 * To use I18nAttributeBehavior, insert the following code to your ActiveRecord class:
 *
 * ```php
 * use mgcode\i18n\common\behaviors\I18nAttributeBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *          [
 *              'class' => I18nAttributeBehavior::className(),
 *              'attributes' => ['name'],
 *              'relation' => 'translations',
 *          ],
 *     ];
 * }
 * ```
 *
 * @link https://github.com/mg-code/yii2-language
 * @author Maris Graudins <maris@mg-interactive.lv>
 * @property \yii\db\ActiveRecord $owner
 */
class I18nAttributeBehavior extends Behavior
{
    /** @var array List of attributes that have translations */
    public $attributes = [];

    /** @var string Relation name which holds all related translations */
    public $relation;

    /**
     * Returns translation model
     * @param $attribute
     * @param string $language
     * @return \yii\base\Model|null
     * @throws InvalidConfigException
     */
    public function getTranslationModel($attribute, $language)
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->lang == $language && $translation->key == $attribute) {
                return $translation;
            }
        }
        return null;
    }

    /**
     * Returns translation model index
     * @param $attribute
     * @param string $language
     * @return \yii\base\Model|null
     * @throws InvalidConfigException
     */
    public function getTranslationIndex($attribute, $language)
    {
        foreach ($this->getTranslations() as $index => $translation) {
            if ($translation->lang == $language && $translation->key == $attribute) {
                return $index;
            }
        }
        return null;
    }

    /**
     * Populate missing translations
     * @throws InvalidConfigException
     */
    public function populateMissingTranslations()
    {
        /** @var string $modelClass */
        $modelClass = $this->owner->getRelation($this->relation)->modelClass;

        $languages = \Yii::$app->languageManager->getSupported(true);
        $translations = $this->getTranslations();
        foreach ($this->attributes as $attribute) {
            foreach ($languages as $language) {
                if (!$this->getTranslationModel($attribute, $language)) {
                    $translations[] = new $modelClass ([
                        'key' => $attribute,
                        'lang' => $language,
                    ]);
                }
            }
        }

        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $owner->populateRelation($this->relation, $translations);
    }

    /**
     * Returns translations
     * @return array
     * @throws InvalidConfigException
     */
    protected function getTranslations()
    {
        if (!$this->relation) {
            throw new InvalidConfigException('`relation` property is not set.');
        }
        return $this->owner->{$this->relation};
    }

    /**
     * Returns value of translated attribute
     * @param $attribute
     * @param mixed $language
     * @return mixed
     */
    public function getTranslatedAttribute($attribute, $language = null, $defaultLanguage = null)
    {
        if ($language === null) {
            $language = \Yii::$app->language;
        }
        $translation = $this->getTranslationModel($attribute, $language);

        // If translation is empty and default language is defined
        if ((!$translation || !trim($translation->value))) {
            if ($defaultLanguage === null) {
                $defaultLanguage = \Yii::$app->languageManager->defaultLanguage;
            }
            if ($defaultLanguage && $language != $defaultLanguage) {
                return $this->getTranslatedAttribute($attribute, $defaultLanguage);
            }
        }

        return $translation ? $translation->value : null;
    }

    /**
     * Whether attribute exists
     * @return bool
     */
    protected function hasI18nAttribute($name)
    {
        return isset($this->attributes[$name]) || in_array($name, $this->attributes);
    }

    /** @inheritdoc */
    public function canGetProperty($name, $checkVars = true)
    {
        if ($this->hasI18nAttribute($name)) {
            return true;
        }
        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * PHP getter magic method.
     * This method is overridden so that attributes can be accessed like properties.
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($this->hasI18nAttribute($name)) {
            return $this->getTranslatedAttribute($name);
        } else {
            return parent::__get($name);
        }
    }

    /**
     * PHP setter magic method.
     * i18n attributes are read-only.
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if ($this->hasI18nAttribute($name)) {
            throw new InvalidCallException('Setting read-only property: '.get_class($this).'::'.$name);
        } else {
            parent::__set($name, $value);
        }
    }

    /** @inheritdoc */
    public function __isset($name)
    {
        try {
            return $this->__get($name) !== null;
        } catch (\Exception $e) {
            return false;
        }
    }
}