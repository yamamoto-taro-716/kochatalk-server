<?php
/* @var \App\View\AppView $this */
/* @var \App\Model\Entity\Account[] $accounts */
$this->assign("title", __("ユーザー抽出"));
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
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><?= __("ユーザー抽出") ?></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <?= $this->Form->create(null, ["type" => "get"]) ?>
                            <div class="form-group">
                                <?= $this->Form->control("id", ["label" => __("会員ID"), "class" => "form-control"]) ?>
                            </div>
                            <div class="form-group">
                                <?= $this->Form->control("nickname", ["label" => __("ニックネーム"), "class" => "form-control"]) ?>
                            </div>
                            <div class="form-group">
                                <label><?= __("性別") ?></label>
                                <?= $this->Form->multiCheckbox("gender", \App\Model\Entity\Account::getGenders(), ["hiddenField" => false]) ?>
                            </div>
                            <div class="form-group">
                                <label>OS</label>
                                <?= $this->Form->multiCheckbox("os", ["android" => "Android", "ios" => "iOS"], ["hiddenField" => false]) ?>
                            </div>
                            <div class="form-group">
                                <label><?= __("会員ステータス") ?></label>
                                <?= $this->Form->multiCheckbox("status", \App\Model\Entity\Account::getStatus(), ["hiddenField" => false]) ?>
                            </div>
                            <div class="form-group">
                                <label><?= __("確認ステータス") ?></label>
                                <?= $this->Form->multiCheckbox("avatar_status", \App\Model\Entity\Account::getAvatarStatus(), ["hiddenField" => false]) ?>
                            </div>
                            <div class="form-group">
                                <label><?= __("画像") ?></label>
                                <?= $this->Form->multiCheckbox("has_avatar", \App\Model\Entity\Account::getHasAvatarStatus(), ["hiddenField" => false]) ?>
                            </div>
                            <div class="form-group">
                                <label><?= __("登録日時") ?></label>
                                <?= $this->Form->control("created_from", ["label" => false, "class" => "form-control", "placeholder" => "From"]) ?>
                                ~
                                <?= $this->Form->control("created_to", ["label" => false, "class" => "form-control", "placeholder" => "To"]) ?>
                            </div>
                            <div class="form-group">
                                <label><?= __("最終ログイン日時") ?></label>
                                <?= $this->Form->control("login_from", ["label" => false, "class" => "form-control", "placeholder" => "From"]) ?>
                                ~
                                <?= $this->Form->control("login_to", ["label" => false, "class" => "form-control", "placeholder" => "To"]) ?>
                            </div>
                            <button type="submit" class="btn btn-info"><?= __("抽出") ?></button>
                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-sm-9 col-xs-12">
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
                                    <th class="column-title"><i class="fa fa-file-text-o"></i></th>
                                    <th class="column-title"><?= __("会員ID") ?></th>
                                    <th class="column-title"><?= __("画像") ?></th>
                                    <th class="column-title"><?= __("情報") ?></th>
                                    <th class="column-title"><?= __("ニックネーム") ?></th>
                                    <th class="column-title"><?= __("性別") ?></th>
                                    <th class="column-title"><?= __("最終ログイン日時") ?></th>
                                    <th class="column-title"><?= __("登録日時") ?></th>
                                    <th class="column-title"><?= __("ステータス") ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($accounts as $k => $account) : ?>
                                <tr>
                                    <td>
                                        <a href="<?= $this->Url->build(["action" => "view", $account->id]) ?>" title="<?= __("Detail") ?>"><i class="fa fa-file-text-o"></i></a>
                                        <a href="<?= $this->Url->build(["action" => "edit", $account->id]) ?>" title="<?= __("Detail") ?>"><i class="fa fa-pencil"></i></a>
                                    </td>
                                    <td><?= $account->id ?></td>
                                    <td>                               	
                                    	<?php if (!empty($account->avatar)) { ?>
                                    	<div class="image">
                                    		<img src="<?= $account->avatar ?>" style="width: 60px" />
                                    	</div>
                                    	<div class="action" style="padding-top:6px">
                                    		<span>
                                    			<a class='btn btn-default btn-sm <?php echo $account->avatar_status == 1 ? "disabled" : ""; ?>' 
                                    				href="<?= $this->Url->build(["action" => "confirmAvatar", $account->id]) ?>">
                                    				<?php echo $account->avatar_status == 1 ? __("確認済み") : __("未確認"); ?>
                                    			</a>
                                    		</span>
                                    		<span>
                                    			<a class="btn btn-default btn-sm" 
                                    				href="<?= $this->Url->build(["action" => "removeAvatar", $account->id]) ?>">
                                    				<?= __("消す") ?>
                                    			</a>
                                			</span>
                                    	</div>
                                    	<?php } ?>                                    	
                                	</td>
                                    <td>
                                        <?php if (!empty($account->age)) { ?>
                                        <p>年齢:<?= $account->age ?>歳
                                        <?php } ?>  
                                        <p>住まい:<?= $account->prefecture ?></p>
                                        <p>自己紹介文:<?= $account->intro ?></p>
                                    </td>
                                    <td><?= $account->nickname ?></td>
                                    <td><?= \App\Model\Entity\Account::getGenders()[$account->gender] ?></td>
                                    <td><?= $account->device->last_access->timezone(\Cake\Core\Configure::read("COMMON.timezone"))->format("Y-m-d H:i:s") ?></td>
                                    <td><?= $account->created->timezone(\Cake\Core\Configure::read("COMMON.timezone"))->format("Y-m-d H:i:s") ?></td>
                                    <td><?= \App\Model\Entity\Account::getStatus()[$account->status] ?></td>
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

<script type="text/javascript">
    $(function () {
        $('#created-from').datetimepicker({
            locale: 'ja',
            format: 'YYYY-MM-DD HH:mm'
        });
        $('#created-to').datetimepicker({
            locale: 'ja',
            format: 'YYYY-MM-DD HH:mm'
        });
        $("#created-from").on("dp.change", function (e) {
            $('#created-to').data("DateTimePicker").minDate(e.date);
        });
        $("#created-to").on("dp.change", function (e) {
            $('#created-from').data("DateTimePicker").maxDate(e.date);
        });
        $('#login-from').datetimepicker({
            locale: 'ja',
            format: 'YYYY-MM-DD HH:mm'
        });
        $('#login-to').datetimepicker({
            locale: 'ja',
            format: 'YYYY-MM-DD HH:mm'
        });
        $("#login-from").on("dp.change", function (e) {
            $('#login-to').data("DateTimePicker").minDate(e.date);
        });
        $("#login-to").on("dp.change", function (e) {
            $('#login-from').data("DateTimePicker").maxDate(e.date);
        });
        
    });
</script>