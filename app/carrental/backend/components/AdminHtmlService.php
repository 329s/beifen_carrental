<?php

namespace backend\components;

/**
 * Description of AdminHtmlService
 *
 * @author kevin
 */
class AdminHtmlService {
    
    public static function renderControlSidebar()
    {
        $htmlArray = [];
        $htmlArray[] = \yii\bootstrap\Html::style(".control-sidebar-title { padding:6px 0; margin-top:6px; }");
        $authoration = AdminModule::getCurRoleAuthoration();
        if ($authoration < \backend\models\Rbac_role::AUTHORITY_DOMAIN_MANAGER) {
            return implode("\n", $htmlArray);
        }
        
        $arrTabsData = self::getControlSidebarTabsData();
        
        // Control Sidebar
        $htmlArray[] = \yii\helpers\Html::beginTag('aside', ['class'=>'control-sidebar control-sidebar-dark']);
        
        // Create the tabs
        $htmlArray[] = \yii\helpers\Html::beginTag('ul', ['class'=>'nav nav-tabs nav-justified control-sidebar-tabs']);
        foreach ($arrTabsData as $tabData) {
            $htmlArray[] = \yii\helpers\Html::tag('li', \yii\helpers\Html::a(\yii\helpers\Html::tag('i', '', ['class'=>"{$tabData['icon']}"]), "#control-sidebar-{$tabData['tag']}-tab", ['data-toggle'=>'tab']));
        }
        $htmlArray[] = \yii\helpers\Html::endTag('ul');
        
        // Tab panes
        $htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'tab-content']);
        $activePane = ' active';
        foreach ($arrTabsData as $tabData) {
            $htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>"tab-pane{$activePane}", 'id'=>"control-sidebar-{$tabData['tag']}-tab"]);
            $htmlArray[] = \yii\helpers\Html::tag('h3', $tabData['title'], ['class'=>'control-sidebar-heading control-sidebar-title']);
            foreach ($tabData['bodys'] as $bodyInfo) {
                if ($bodyInfo['type'] == 'menu') {
                    $htmlArray[] = \yii\helpers\Html::beginTag('ul', ['class'=>'control-sidebar-menu']);
                    
                    foreach ($bodyInfo['data'] as $menu) {
                        $menuBtnOptions = ['iconClass'=>"menu-icon {$menu['icon']}", 'wrapperClass'=>'menu-info', 'titleClass'=>'control-sidebar-subheading'];
                        if (isset($menu['subtitle'])) {
                            $menuBtnOptions['subtitle'] = $menu['subtitle'];
                        }
                        $htmlArray[] = \yii\bootstrap\Html::tag('li', self::renderItemButton($menu['title'], $menu['link'], $menuBtnOptions, \yii\helpers\ArrayHelper::getValue($menu, 'target', 'document')), []);
                    }
                    
                    $htmlArray[] = \yii\helpers\Html::endTag('ul');
                }
                else {
                    // TODO
                }
            }
            $htmlArray[] = \yii\helpers\Html::endTag('div');
            
            if ($activePane != '') {
                $activePane = '';
            }
        }
        $htmlArray[] = \yii\helpers\Html::endTag('div');
        
        // ./Control Sidebar
        $htmlArray[] = \yii\helpers\Html::endTag('aside');
        
        $htmlArray[] = \yii\helpers\Html::tag('div', '', ['class'=>'control-sidebar-bg']);
        
        return implode("\n", $htmlArray);
    }
    
    public static function renderItemButton($title, $openUrl = '', $options = [], $target = 'document') {
        $htmlArray = [];
        $linkOptions = \yii\helpers\ArrayHelper::getValue($options, 'linkOptions', []);
        if ($target == 'blank' || $target == '_blank') {
            $linkOptions['target'] = '_blank';
        }
        elseif ($target == 'document') {
            $linkOptions['href'] = \yii\helpers\ArrayHelper::getValue($linkOptions, 'href', $openUrl);
        }
        else {
            $linkOptions['href'] = \yii\helpers\ArrayHelper::getValue($linkOptions, 'href', 'javascript:void(0)');
            $linkOptions['onclick'] = \yii\helpers\ArrayHelper::getValue($linkOptions, 'onclick', (empty($openUrl)?'':"$.custom.bootstrap.loadElement('#{$target}', '{$openUrl}');"));
        }
        $htmlArray[] = \yii\bootstrap\Html::beginTag('a', $linkOptions);
        $htmlArray[] = \yii\bootstrap\Html::tag('i', '', ['class'=> \yii\helpers\ArrayHelper::getValue($options, 'iconClass', 'fa fa-dashboard')]);
        $wrapperClass = \yii\helpers\ArrayHelper::getValue($options, 'wrapperClass', '');
        $titleClass = \yii\helpers\ArrayHelper::getValue($options, 'titleClass', '');
        if (!empty($titleClass) || !empty($wrapperClass)) {
            $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class'=>$wrapperClass]);
            $htmlArray[] = \yii\bootstrap\Html::tag('h4', $title, ['class'=>$titleClass]);
            $description = \yii\helpers\ArrayHelper::getValue($options, 'subtitle', false);
            if ($description) {
                $htmlArray[] = \yii\bootstrap\Html::tag('p', $description);
            }
            $htmlArray[] = \yii\bootstrap\Html::endTag('div');
        }
        else {
            $htmlArray[] = \yii\bootstrap\Html::tag('span', $title);
        }
        $htmlArray[] = \yii\bootstrap\Html::endTag('a');
        return implode("\n", $htmlArray);
    }
    
    public static function getControlSidebarTabsData() {
        $authoration = AdminModule::getCurRoleAuthoration();
        if ($authoration < \backend\models\Rbac_role::AUTHORITY_DOMAIN_MANAGER) {
            return implode("\n", $htmlArray);
        }
        
        $defaultTarget = \common\helpers\BootstrapHtml::MAIN_CONTENT_ID;
        
        //
        $arrTabsData = [
            [
                'title'=> \Yii::$app->params['app.management.name'],
                'icon' => 'fa fa-home',
                'tag' => 'home',
                'bodys' => [
                    [
                        'type' => 'menu',
                        'data' => [
                            
                        ]
                    ],
                ],
            ],
            [
                'title'=> \Yii::t('locale', 'System options'),
                'icon' => 'fa fa-gears',
                'tag' => 'settings',
                'bodys' => [
                    [
                        'type' => 'menu',
                        'data' => [
                            
                        ]
                    ],
                ],
            ],
        ];
        if ($authoration >= \backend\models\Rbac_role::AUTHORITY_ADMINISTRATOR) {
            $arrTabsData[] = [
                'title'=> \Yii::t('locale', 'System maintenance'),
                'icon' => 'fa fa-wrench',
                'tag' => 'wrenches',
                'bodys' => [
                    [
                        'type' => 'menu',
                        'data' => [
                            [
                                'title' => \Yii::t('locale', 'Authority configuration'),
                                'subtitle' => '',
                                'link' => \yii\helpers\Url::to(['/rbac/permissions/index']),
                                'icon' => 'fa fa-lock bg-red',
                                'target' => $defaultTarget,
                            ],
                            [
                                'title' => \Yii::t('locale', 'System maintenance'),
                                'subtitle' => '执行版本升级、数据升级与维护、重置缓存、系统测试等维护操作。',
                                'link' => \yii\helpers\Url::to(['/sysmaintenance/default/index']),
                                'icon' => 'fa fa-gear bg-blue',
                                'target' => 'document',
                            ],
                            [
                                'title' => \Yii::t('locale', 'Developing test'),
                                'subtitle' => '系统开发过程中测试入口。',
                                'link' => \yii\helpers\Url::to(['/sysmaintenance/test/index']),
                                'icon' => 'fa fa-bug bg-yellow',
                                'target' => 'document',
                            ],
                        ],
                    ],
                ],
            ];
        }
        
        return $arrTabsData;
    }
    
    public static function getViewPrefix() {
        if (isset(\Yii::$app->params['app.ui.type']) && \Yii::$app->params['app.ui.type'] == 'easyui') {
            return 'easyui';
        }
        return false;
    }
    
    public static function getAdminDisplayInfoArray() {
        $urlRoot = \common\helpers\Utils::getRootUrl();
        $logoMiniUrl = "{$urlRoot}assets/images/logo/logo_yika_48x48.png";
        $logoUrl = "{$urlRoot}assets/images/logo/yikazc.png";

        $authOfficeName = \backend\components\AdminModule::getAdminAuthOfficeDisplayName();
        if (\Yii::$app->user->isGuest) {
            $adminName = \Yii::t('locale', 'Not signed in');
            $adminRole = \Yii::t('locale', 'Not signed in');
            $adminAvatarUrl = "{$urlRoot}assets/images/user/avatar04.png";
        }
        else {
            $objAdmin = \backend\models\Rbac_admin::findIdentity(\Yii::$app->user->id);
            $adminName = \Yii::$app->user->identity->username;
            $adminRole = \backend\components\AdminModule::getCurAuthRoleDisplayName();
            if ($objAdmin) {
                $adminAvatarUrl = $objAdmin->avatar;
            }
            else {
                $adminAvatarUrl = "{$urlRoot}assets/images/user/avatar5.png";
            }
        }
        
        return [
            'name' => $adminName,
            'role' => $adminRole,
            'avatarUrl' => $adminAvatarUrl,
            'logoMiniUrl' => $logoMiniUrl,
            'logoUrl' => $logoUrl,
            'office' => $authOfficeName,
        ];
    }
    
    public static function getNavTabNoticeItems($arrAdminInfo) {
        $menuItems = [];
        $notices = \backend\components\NoticeService::currentlyStatus();
        // Messages
        $newMessages = [
            ['title' => \Yii::t('carrental', 'Universal notification'), 'time' => \Yii::t('locale', '{num} mins', ['num'=>5]),
                'desc'=> \Yii::t('carrental', 'This is a normal notification'),
                'image'=>['src'=>$arrAdminInfo['avatarUrl'], 'options'=>['alt'=>$arrAdminInfo['role']]],
                'linkOptions' => [],
                'hide'=>true,
            ],
        ];
        $newMessageHtmls = [];
        $messageCount = 0;
        foreach ($newMessages as $msgInfo) {
            if (!$msgInfo['hide']) {
                $messageCount++;
            }
            $newMessageHtmls[] = \common\helpers\BootstrapAdminHtml::renderNotificationMessageElement([
                'title'=>$msgInfo['title'], 'time'=>$msgInfo['time'], 'description'=>$msgInfo['desc'], 
                'image'=>$msgInfo['image'], 
                'linkOptions'=>$msgInfo['linkOptions'], 'hide'=>$msgInfo['hide']]);
        }
        $messageItem = [
            'label' => \yii\bootstrap\Html::tag('i', '', ['class'=>'fa fa-envelope-o']).\yii\bootstrap\Html::tag('span', $messageCount ? $messageCount :'', ['class'=>'label label-success']),
            'options' => ['class'=>'dropdown messages-menu'],
            //'linkOptions' => ['class'=>'dropdown-toggle'],
            'dropDownOptions' => ['class'=>'dropdown-menu'],
            'items' => [
                \yii\helpers\Html::tag('li', \Yii::t('locale', 'You have {number} {names}', ['number'=>$messageCount, 'names'=>\Yii::t('locale', 'messages')]), ['class'=>'header']),
                \yii\bootstrap\Html::tag('li', \yii\bootstrap\Html::tag('ul', implode("\n", $newMessageHtmls), ['class'=>'menu'])),
                ['label'=> \Yii::t('locale', 'See all {names}', ['names'=>\Yii::t('locale', 'messages')]), 'options'=>['class'=>'footer']],
            ],
            'encode' => false,
        ];
        $menuItems[] = $messageItem;

        // Notifications
        $newNotifications = [
            ['message'=>'有新的待分配车辆订单未处理', 'icon'=>'fa-bullhorn text-aqua',
                'linkOptions' => ['href'=>'javascript:void(0)'], 'key'=>'order-waiting-count'],
            ['message'=>'有待出车订单未处理', 'icon'=>'fa-arrow-circle-o-right text-aqua',
                'linkOptions' => ['href'=>'javascript:void(0)'], 'key'=>'order-dispatching-count'],
            ['message'=>'有待还车订单未处理', 'icon'=>'fa-arrow-circle-o-left text-aqua',
                'linkOptions' => ['href'=>'javascript:void(0)'], 'key'=>'order-returning-count'],
        ];
        $newNotificationHtmls = [];
        $notificationCount = 0;
        foreach ($newNotifications as $msgInfo) {
            if ($notices[$msgInfo['key']]) {
                $notificationCount++;
            }
            $newNotificationHtmls[] = \common\helpers\BootstrapAdminHtml::renderNotificationMessageElement([
                'message'=>$msgInfo['message'], 'icon'=>$msgInfo['icon'], 'linkOptions'=>$msgInfo['linkOptions'],
                'tag'=>$notices[$msgInfo['key']], 'id'=>'header-notifications-'.$msgInfo['key'], 'hide'=>($notices[$msgInfo['key']]==0)
            ]);
        }
        $notificationItem = [
            'label' => \yii\bootstrap\Html::tag('i', '', ['class'=>'fa fa-bell-o']).\yii\bootstrap\Html::tag('span', $notificationCount ? $notificationCount : '', ['class'=>'label label-warning', 'id'=>'header-notifications-count-label']),
            'options' => ['class'=>'dropdown notifications-menu', 'id'=>'header-notifications-menu'],
            //'linkOptions' => ['class'=>'dropdown-toggle'],
            'dropDownOptions' => ['class'=>'dropdown-menu'],
            'items' => [
                \yii\helpers\Html::tag('li', \Yii::t('locale', 'You have {number} {names}', ['number'=> \yii\bootstrap\Html::tag('span', $notificationCount, ['id'=>'header-notifications-count']), 'names'=>\Yii::t('locale', 'notifications')]), ['class'=>'header']),
                \yii\bootstrap\Html::tag('li', \yii\bootstrap\Html::tag('ul', implode("\n", $newNotificationHtmls), ['class'=>'menu'])),
                ['label'=> \Yii::t('locale', 'See all {names}', ['names'=>\Yii::t('locale', 'notifications')]), 'options'=>['class'=>'footer']],
            ],
            'encode' => false,
        ];
        $menuItems[] = $notificationItem;

        // Tasks
        $newTasks = [
            ['title'=>'The task center is waiting to be completed', 'percent'=>20, 'color'=>'aqua', 'hide'=>true]
        ];
        $newTaskCount = 0;
        $newTaskHtmls = [];
        foreach ($newTasks as $msgInfo) {
            if (!$msgInfo['hide']) {
                $newTaskCount++;
            }
            $newTaskHtmls[] = \yii\bootstrap\Html::tag('li', 
                \yii\bootstrap\Html::tag('a', 
                \yii\bootstrap\Html::tag('h3', $msgInfo['title'].\yii\bootstrap\Html::tag('small', "{$msgInfo['percent']}%", ['class'=>'pull-right'])).\common\helpers\BootstrapAdminHtml::progressBar($msgInfo['percent'], isset($msgInfo['options']) ? $msgInfo['options']:[])
                , ['href'=>'#', 'style'=>$msgInfo['hide']?"display:none":''])
            );
        }
        $taskItem = [
            'label' => \yii\bootstrap\Html::tag('i', '', ['class'=>'fa fa-flag-o']).\yii\bootstrap\Html::tag('span', $newTaskCount ? $newTaskCount : '', ['class'=>'label label-danger']),
            'options' => ['class'=>'dropdown tasks-menu'],
            //'linkOptions' => ['class'=>'dropdown-toggle'],
            'dropDownOptions' => ['class'=>'dropdown-menu'],
            'items' => [
                \yii\helpers\Html::tag('li', \Yii::t('locale', 'You have {number} {names}', ['number'=>$newTaskCount, 'names'=>\Yii::t('locale', 'tasks')]), ['class'=>'header']),
                \yii\bootstrap\Html::tag('li', \yii\bootstrap\Html::tag('ul', implode("\n", $newTaskHtmls), ['class'=>'menu'])),
                ['label'=> \Yii::t('locale', 'See all {names}', ['names'=>\Yii::t('locale', 'tasks')]), 'options'=>['class'=>'footer']],
            ],
            'encode' => false,
        ];
        $menuItems[] = $taskItem;
        return $menuItems;
    }
    
    public static function getNavTabUserItem($arrAdminInfo) {
        $userInfoRows = [];
        $userInfoRows[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::tag('a', \Yii::t('locale', 'Office'), ['href'=>'javascript:void(0)']), ['class'=>'col-xs-6 text-center']);
        $userInfoRows[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::tag('a', $arrAdminInfo['office'], ['href'=>'javascript:void(0)']), ['class'=>'col-xs-6 text-center']);
        $userFooters = [];
        if (\Yii::$app->user->isGuest) {
            $userFooters[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::a(\Yii::t('locale', 'Login'), \yii\helpers\Url::to(['/site/login']), ['class'=>'btn btn-default btn-flat']), ['class'=>'pull-right']);
        }
        else {
            $userFooters[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::a(\Yii::t('locale', 'Profile'), 'javascript:void(0)', ['class'=>'btn btn-default btn-flat']), ['class'=>'pull-left']);
            $userFooters[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::a(\Yii::t('locale', 'Logout'), \yii\helpers\Url::to(['/site/logout']), ['data-method' => 'post', 'class'=>'btn btn-default btn-flat']), ['class'=>'pull-right']);
        }

        $userItem = [
            'label' => \yii\bootstrap\Html::img($arrAdminInfo['avatarUrl'], ['class'=>'user-image', 'alt'=>$arrAdminInfo['name']]).\yii\bootstrap\Html::tag('span', \Yii::$app->user->isGuest?\Yii::t('locale', 'Login'):$arrAdminInfo['name'], ['class'=>'hidden-xs']),
            'options' => ['class'=>'dropdown user user-menu'],
            'dropDownOptions' => ['class'=>'dropdown-menu'],
            'items' => [
                \yii\helpers\Html::tag('li', \yii\bootstrap\Html::img($arrAdminInfo['avatarUrl'], ['class'=>'img-circle', 'alt'=>$arrAdminInfo['name']]).
                    \yii\bootstrap\Html::tag('p', $arrAdminInfo['name'] . \yii\bootstrap\Html::tag('small', (\Yii::$app->user->isGuest?'':$arrAdminInfo['role']))), 
                    ['class'=>'user-header']),
                \yii\helpers\Html::tag('li', \yii\bootstrap\Html::tag('div', implode("\n", $userInfoRows), ['class'=>'row']), ['class'=>'user-body']),
                \yii\helpers\Html::tag('li', implode("\n", $userFooters), 
                    ['class'=>'user-footer']),
            ],
            'encode' => false,
        ];
        return $userItem;
    }
    
}
