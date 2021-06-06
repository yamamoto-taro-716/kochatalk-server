<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $this->fetch("title") ?> | <?= __("Balloon SNS CMS") ?></title>

    <!-- Bootstrap -->
    <?= $this->Html->css("bootstrap.min") ?>
    <!-- Font Awesome -->
    <?= $this->Html->css("font-awesome.min") ?>
    <!-- NProgress -->
    <?= $this->Html->css("nprogress") ?>
    <!-- PNotify -->
    <?= $this->Html->css("pnotify/pnotify") ?>
    <?= $this->Html->css("pnotify/pnotify.buttons") ?>

    <!-- Custom Theme Style -->
    <?= $this->Html->css("custom") ?>
</head>

<body class="login">
<!-- jQuery -->
<?= $this->Html->script("jquery.min") ?>
<!-- Bootstrap -->
<?= $this->Html->script("bootstrap.min") ?>
<?= $this->fetch("content") ?>
<?= $this->Flash->render() ?>
<!-- NProgress -->
<?= $this->Html->script("nprogress") ?>
<!-- PNotify -->
<?= $this->Html->script("pnotify/pnotify") ?>
<?= $this->Html->script("pnotify/pnotify.buttons") ?>
</body>
</html>
