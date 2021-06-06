<?php
/* @var \App\View\AppView $this */
/* @var \App\Model\Entity\AccountReport[] $account_reports */
$this->assign("title", __("通報"));
?>
<style>
    .checkbox{
        margin-top: 0;
    }
</style>
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3><?= $this->fetch("title") ?></h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><?= __("目録") ?></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <?= $this->element('pagination') ?>
                        <div class="table-responsive">
                            <table class="table table-striped jambo_table bulk_action">
                                <thead>
                                <tr class="headings">
                                    <th class="column-title">#</th>
                                    <th class="column-title"><?= __("User was reported") ?></th>
                                    <th class="column-title"><?= __("Reported by") ?></th>
                                    <th class="column-title"><?= __("登録日時") ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($account_reports as $key => $account) : ?>
                                    <tr>
                                        <td><?= ($this->Paginator->param("page") - 1) * $this->Paginator->param("perPage") + $key + 1 ?></td>
                                        <td>
                                            <a href="<?= $this->Url->build(["controller" => "Accounts", "action" => "view", $account->account_receive_id]) ?>">
                                                <?= $account->account_receife->nickname ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= $this->Url->build(["controller" => "Accounts", "action" => "view", $account->account_action_id]) ?>">
                                                <?= $account->account_action->nickname ?>
                                            </a>
                                        </td>
                                        <td><?= $account->modified->format("Y-m-d H:i:s") ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?= $this->element('pagination') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>