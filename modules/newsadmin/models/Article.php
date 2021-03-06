<?php

namespace newsadmin\models;

use Yii;

class Article extends \admin\ngrest\base\Model
{

    public static function tableName()
    {
        return 'news_article';
    }

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'eventBeforeInsert']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'eventBeforeUpdate']);
    }

    public function scenarios()
    {
        return [
           'restcreate' => ['title', 'text', 'cat_id', 'image_id', 'image_list', 'tags', 'timestamp_display_from', 'timestamp_display_until', 'file_list'],
           'restupdate' => ['title', 'text', 'cat_id', 'image_id', 'image_list', 'tags', 'timestamp_create', 'timestamp_display_from', 'timestamp_display_until', 'is_display_limit', 'file_list'],
       ];
    }

    public function rules()
    {
        return [
            [['cat_id', 'title', 'text' ], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
          'cat_id' => 'Kategorie',
          'title' => 'Titel',
          'text' => 'Beschreibung',
        ];
    }

    public function eventBeforeUpdate()
    {
        $this->update_user_id = Yii::$app->adminuser->getId();
        $this->timestamp_update = time();
    }

    public function eventBeforeInsert()
    {
        $this->create_user_id = Yii::$app->adminuser->getId();
        $this->update_user_id = Yii::$app->adminuser->getId();
        $this->timestamp_create = time();
        $this->timestamp_update = time();
    }

    public function getDetailUrl($contextNavItemId = null)
    {
        if ($contextNavItemId) {
            return \luya\helpers\Url::toModule($contextNavItemId, 'news/default/detail', ['id' => $this->id, 'title' => \yii\helpers\Inflector::slug($this->title)]);
        }

        return \luya\helpers\Url::to('news/default/detail', ['id' => $this->id, 'title' => \yii\helpers\Inflector::slug($this->title)]);
    }

    public static function getAvailableNews()
    {
        $articles = Article::find()->where('timestamp_display_from <= :time',['time' => time()])->all();
        
        // filter if display time is limited
        foreach($articles as $key => $article) {
            if ($article->is_display_limit) {
                if ($article->timestamp_display_until <= time()) {
                    unset($articles[$key]);
                }
            }
        }

        return $articles;
    }

    public function getCategoryName() 
    {
        $catModel = Cat::find()->where(['id' => $this->cat_id])->one();
        return $catModel->title;
    }

    // ngrest

    public $tags = []; // cause of extra fields - will pe parsed trough the ngrest plugins.

    public $i18n = ['title', 'text'];

    public $extraFields = ['tags'];

    public function ngRestApiEndpoint()
    {
        return 'api-news-article';
    }

    public function ngRestConfig($config)
    {
        $config->list->field('cat_id', 'Kategorie')->selectClass('\newsadmin\models\Cat', 'id', 'title');
        $config->list->field('title', 'Titel')->text();
        $config->list->field('timestamp_create', 'Datum')->date();

        $config->update->field('cat_id', 'Kategorie')->selectClass('\newsadmin\models\Cat', 'id', 'title');
        $config->update->field('title', 'Titel')->text();
        $config->update->field('text', 'Beschreibung')->textarea();
        $config->update->field('timestamp_create', 'News erstellt am:')->date();
        $config->update->field('timestamp_display_from', 'News anzeigen ab')->date();

        $config->update->field('is_display_limit', 'News Anzeige zeitlich einschränken:')->toggleStatus();
        $config->update->field('timestamp_display_until', 'News anzeigen bis')->date();
        $config->update->field('image_id', 'Bild')->image();
        $config->update->field('image_list', 'Bild Liste')->imageArray();
        $config->update->field('file_list', 'Datei Liste')->fileArray();
        
        $config->update->extraField('tags', 'Tags')->checkboxRelation(\newsadmin\models\Tag::className(), 'news_article_tag', 'article_id', 'tag_id', ['title']);

        $config->delete = true;

        $config->create->copyFrom('update', ["timestamp_display_until"]);


        return $config;
    }
}
