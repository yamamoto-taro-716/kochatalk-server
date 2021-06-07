<?php /* @var \App\View\AppView $this */ ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $this->fetch("title") ?> | Balloon SNS - CMS</title>

    <!-- Bootstrap -->
    <?= $this->Html->css("bootstrap.min") ?>
    <!-- Font Awesome -->
    <?= $this->Html->css("font-awesome.min") ?>
    <!-- NProgress -->
    <?= $this->Html->css("nprogress") ?>
    <!-- iCheck -->
    <?= $this->Html->css("iCheck/skins/flat/green") ?>
    <!-- PNotify -->
    <?= $this->Html->css("pnotify/pnotify") ?>
    <?= $this->Html->css("pnotify/pnotify.buttons") ?>
    <!-- Datetime-picker -->
    <?= $this->Html->css("bootstrap-datetimepicker.min") ?>

    <!-- Custom Theme Style -->
    <?= $this->Html->css("custom") ?>
</head>

<body class="nav-md">

<!-- jQuery -->
<?= $this->Html->script("jquery.min") ?>
<!-- Bootstrap -->
<?= $this->Html->script("bootstrap.min") ?>
<?= $this->Html->script("moment-with-locales") ?>
<?= $this->Html->script("moment-timezone-with-data") ?>
<!-- Bootstrap -->
<?= $this->Html->script("bootstrap-datetimepicker.min") ?>

<div class="container body">
    <div class="main_container">
        <div class="col-md-3 left_col">
            <div class="left_col scroll-view">
                <div class="navbar nav_title text-center" style="border: 0;">
                    <a href="#" class="site_title"><span>kochatalk</span></a>
                </div>

                <div class="clearfix"></div>

                <!-- sidebar menu -->
                <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                    <div class="menu_section">

                        <ul class="nav side-menu">
                            <li>
                                <a href="<?= $this->Url->build(["controller" => "Accounts", "action" => "index"]) ?>"><i class="fa fa-users"></i> ユーザー抽出</a>
                            </li>
                            <li>
                                <a href="<?= $this->Url->build(["controller" => "Message", "action" => "index"]) ?>"><i class="fa fa-comments-o"></i> メッセージログ管理</a>
                            </li>
                            <li>
                                <a href="<?= $this->Url->build(["controller" => "Report", "action" => "index"]) ?>"><i class="fa fa-flag-o"></i> 通報</a>
                            </li>
                            <li>
                                <a href="<?= $this->Url->build(["controller" => "PushNotification", "action" => "index"]) ?>"><i class="fa fa-paper-plane-o"></i> プッシュ通知</a>
                            </li>
                            <li>
                                <a href="<?= $this->Url->build(["controller" => "Contact", "action" => "index"]) ?>"><i class="fa fa-envelope-o"></i> 問い合わせ</a>
                            </li>
                            <li>
                                <a href="<?= $this->Url->build(["controller" => "RandomConfigs", "action" => "index"]) ?>"><i class="fa fa-comment-o"></i> ランダムチャット送信</a>
                            </li>
                            <li>
                                <a href="<?= $this->Url->build(["controller" => "Settings", "action" => "index"]) ?>"><i class="fa fa-gears"></i> 設定</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /sidebar menu -->
            </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
            <div class="nav_menu">
                <nav>
                    <div class="nav toggle">
                        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                    </div>

                    <ul class="nav navbar-nav navbar-right">
                        <li class="">
                            <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown"
                               aria-expanded="false">
                                <?= $this->request->getSession()->read("Auth.User.username") ?>
                                <span class=" fa fa-angle-down"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-usermenu pull-right">
                                <li><a href="<?= $this->Url->build(["controller" => "Users", "action" => "profile"]) ?>"> Profile</a></li>
                                <li><a href="<?= $this->Url->build(["controller" => "Users", "action" => "logout"]) ?>"> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
        <?= $this->fetch("content") ?>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
            <div class="pull-right">
                Balloon SNS CMS - Developed by <a href="https://pacom-software.com">PACOM Sofware</a>
            </div>
            <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
    </div>
</div>

<?= $this->Flash->render() ?>

<!-- FastClick -->
<?= $this->Html->script("fastclick") ?>
<!-- NProgress -->
<?= $this->Html->script("nprogress") ?>
<!-- iCheck -->
<?= $this->Html->script("icheck.min") ?>
<!-- PNotify -->
<?= $this->Html->script("pnotify/pnotify") ?>
<?= $this->Html->script("pnotify/pnotify.buttons") ?>
<!-- Autosize -->
<?= $this->Html->script("autosize.min") ?>
<script>
    $(function(){
        autosize($('.resizable_textarea'));
    })
</script>

<!-- Custom Theme Scripts -->
<?= $this->Html->script("custom.min") ?>
</body>
</html>
