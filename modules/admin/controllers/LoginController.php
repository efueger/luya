<?php

namespace admin\controllers;

use Yii;
use \luya\helpers\Url;
use \yii\helpers\Url as YiiUrl;

class LoginController extends \admin\base\Controller
{
    public $layout = '@admin/views/layouts/nosession';

    public $skipModuleAssets = ["*"];
    
    public $assets = ["\admin\assets\Login"];
    
    public function getRules()
    {
        return [
            [
                'allow' => true,
                'actions' => ['index'],
                'roles' => ['?', '@'],
            ],
        ];
    }

    public function actionIndex()
    {
        $url = YiiUrl::to(Url::to('admin'), true);
        
        // redirect logged in users
        if (!Yii::$app->adminuser->isGuest) {
            return $this->redirect($url);
        }

        // get the login form model
        $model = new \admin\models\LoginForm();
        // see if values are sent via post
        if (isset($_POST['login'])) {
            $model->attributes = $_POST['login'];
            if (($userObject = $model->login()) !== false) {
                if (Yii::$app->adminuser->login($userObject)) {
                    return $this->redirect($url);
                }
            }
        }

        $this->view->registerJs("$('#email').focus();");
        return $this->render('index', ['model' => $model]);
    }
}
