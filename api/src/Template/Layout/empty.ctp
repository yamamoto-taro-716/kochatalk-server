<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $this->fetch("title") ?></title>
    <!-- Bootstrap -->
    <?= $this->Html->css("bootstrap.min") ?>
    <style>
        body {
            background-color: transparent!important
        }
        .container {
            background-color: transparent!important
        }
    </style>
</head>
<body>
    <div class="container">
        <?= $this->fetch("content"); ?>
    </div>
</body>
</html>