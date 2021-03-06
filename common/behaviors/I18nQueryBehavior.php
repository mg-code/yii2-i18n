<?php

namespace mgcode\i18n\common\behaviors;

use mgcode\helpers\ActiveQueryAliasTrait;
use yii\base\Behavior;
use yii\db\ActiveQuery;

/**
 * Class I18nQueryBehavior
 *
 * @link https://github.com/mg-code/yii2-language
 * @author Maris Graudins <maris@mg-interactive.lv>
 * @property \yii\db\ActiveRecord $owner
 */
class I18nQueryBehavior extends Behavior
{
    use ActiveQueryAliasTrait;

    /** @var string Relation name which holds all related translations */
    public $relation;

    /** @var int Alias count */
    protected $aliases = 0;

    /**
     * Selects record by translated attribute
     * @param string $attribute
     * @param string $value
     * @param string|bool $matchLanguage
     * @return ActiveQuery
     */
    public function byI18nAttribute($attribute, $value, $matchLanguage = false)
    {
        if ($matchLanguage === true) {
            $matchLanguage = \Yii::$app->language;
        }

        /** @var ActiveQuery $query */
        $query = $this->owner;
        $alias = 'i18n_'.$this->aliases++;

        $query->joinWith([
            $this->relation.' '.$alias => function ($query) use ($alias, $attribute, $value, $matchLanguage) {
                /** @var $query ActiveQuery */
                $query->andWhere([
                    $alias.'.key' => $attribute,
                    $alias.'.value' => $value,
                ]);
                if ($matchLanguage) {
                    $query->andWhere([$alias.'.lang' => $matchLanguage]);
                }
            }
        ], false, 'JOIN');
        return $query;
    }

    /**
     * Orders by translated attribute
     * @param string $attribute
     * @param int $sort Default sort by ASC.
     * @return ActiveQuery
     */
    public function orderByI18nAttribute($attribute, $sort = SORT_ASC)
    {
        /** @var ActiveQuery $query */
        $query = $this->owner;
        $alias = 'i18n_'.$this->aliases++;

        $query->joinWith([
            $this->relation.' '.$alias => function ($query) use ($alias, $attribute, $sort) {
                /** @var $query ActiveQuery */
                $query
                    ->andWhere([
                        $alias.'.key' => $attribute,
                        $alias.'.lang' => \Yii::$app->language,
                    ])
                    ->orderBy([$alias.'.value' => $sort]);
            }
        ], false, 'LEFT JOIN');

        return $query;
    }
}