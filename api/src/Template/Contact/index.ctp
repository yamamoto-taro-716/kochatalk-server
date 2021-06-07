<?php
/* @var \App\View\AppView $this */
$this->assign("title", __("問い合わせ"));
?>
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3><?= $this->fetch("title") ?></h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><?= $this->fetch("title") ?></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="table-responsive">
                            <table class="table table-striped jambo_table bulk_action">
                                <thead>
                                <tr class="headings">
                                    <th class="column-title"><?= __("会員ID") ?></th>
                                    <th class="column-title"><?= __("ニックネーム") ?></th>
                                    <th class="column-title"><?= __("メッセージ") ?></th>
                                    <th class="column-title"><?= __("登録日時") ?></th>
                                    <th class="column-title"><?= __("ステータス") ?></th>
                                    <th class="column-title"><?= __("#Action") ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($contacts as $k => $contact) : ?>
                                    <tr>
                                        <td>
                                            <a href="<?= $this->Url->build(["controller" => "Accounts", "action" => "view", $contact["account_id"]]) ?>" title="<?= __("Detail") ?>">
                                                <?= $contact["account_id"] ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= $this->Url->build(["controller" => "Accounts", "action" => "view", $contact["account_id"]]) ?>" title="<?= __("Detail") ?>">
                                                <?= $contact["nickname"] ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?= $contact["message"] ?>
                                        </td>
                                        <td>
                                            <?php echo $contact["created"]->toDateTime()->setTimezone(new DateTimeZone(\Cake\Core\Configure::read("COMMON.timezone")))->format("Y-m-d H:i:s") ?>
                                        </td>
                                        <td>
                                            <?= $contact["is_reply"] ? "返信済み" : "未返信" ?>
                                        </td>
                                        <td>
                                            <a href="<?= $this->Url->build(["action" => "conversation", $contact["account_id"]]) ?>" class="btn btn-default btn-sm">
                                                Reply
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>