<?php
/* @var \App\View\AppView $this */
/* @var \App\Model\Entity\RandomConfig $randomConfig */
$this->assign("title", __("ランダムチャット送信"));
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
						<h2><?= __("ランダムチャット送信") ?></h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						<?= $this->Form->create($randomConfig, ["id" => "frmSetting", "class" => "form-horizontal form-label-left"]); ?>
                        <div class="form-group">
                            <label class="control-label col-sm-3">
                                登録日時設定
                            </label>
                            <div class="col-sm-3">
                                <?= $this->Form->control("created_type", ["type" => "select", "options" => \App\Model\Entity\RandomConfig::getTypeArray(), "label" => false, "class" => 'form-control']) ?>
                            </div>
                            <div id="created" style="display: none">
                                <label class="control-label col-sm-3">
                                    日付 / 日
                                </label>
                                <div class="col-sm-3">
                                    <?= $this->Form->control("created_value", ["label" => false, "class" => "form-control"]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-3">
                                最終アクセス
                            </label>
                            <div class="col-sm-3">
								<?= $this->Form->control("access_type", ["type" => "select", "options" => \App\Model\Entity\RandomConfig::getTypeArray(), "label" => false, "class" => 'form-control']) ?>
                            </div>
                            <div id="access" style="display: none">
                                <label class="control-label col-sm-3">
                                    日付 / 日
                                </label>
                                <div class="col-sm-3">
                                    <?= $this->Form->control("access_value", ["label" => false, "class" => "form-control"]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-3">
                                受信数
                            </label>
                            <div class="col-sm-3">
								<?= $this->Form->control("random_limit", ["label" => false, "class" => "form-control"]) ?>
                            </div>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <label class="control-label col-sm-3">

                            </label>
                            <div class="col-sm-3">
								<button type="submit" class="btn btn-success">Save</button>
                                <a href="<?= $this->Url->build(["action" => "index"]) ?>" class="btn btn-default">Cancel</a>
                            </div>
                        </div>
                        <?= $this->Form->end(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
    $(function(){
        loadType();

        $("#created-type").on("change", function(){
            var created_type = parseInt($("#created-type").val()) || 0;
            if (created_type != 1) {
                $("#created").show();
                if (created_type == 2) {
                    $("#created label").text("日付");
                    $('#created input').val('');
                    $('#created input').datetimepicker({
                        locale: 'ja',
                        format: 'YYYY-MM-DD HH:mm'
                    });
                } else {
                    $("#created label").text("日");
                    $('#created input').datetimepicker("destroy");
                    $('#created input').val(0);
                }
            } else {
                $("#created").hide();
            }
        });
        $("#access-type").on("change", function(){
            var access_type = parseInt($("#access-type").val()) || 0;
            if (access_type != 1) {
                $("#access").show();
                if (access_type == 2) {
                    $("#access label").text("日付");
                    $('#access input').val('');
                    $('#access input').datetimepicker({
                        locale: 'ja',
                        format: 'YYYY-MM-DD HH:mm'
                    });
                } else {
                    $("#access label").text("日");
                    $('#access input').datetimepicker("destroy");
                    $('#access input').val(0);
                }
            } else {
                $("#access").hide();
            }
        });

        function loadType() {
            var created_type = parseInt($("#created-type").val()) || 0;
            var access_type = parseInt($("#access-type").val()) || 0;

            if (created_type != 1) {
                $("#created").show();
                if (created_type == 2) {
                    $("#created label").text("日付");
                    $('#created input').datetimepicker({
                        locale: 'ja',
                        format: 'YYYY-MM-DD'
                    });
                } else {
                    $("#created label").text("日");
                }
            } else {
                $("#created").hide();
            }
            if (access_type != 1) {
                $("#access").show();
                if (access_type == 2) {
                    $("#access label").text("日付");
                    $('#access input').datetimepicker({
                        locale: 'ja',
                        format: 'YYYY-MM-DD'
                    });
                } else {
                    $("#access label").text("日");
                }
            } else {
                $("#access").hide();
            }

        }

        $("#frmSetting").on("submit", function(){
            var created_type = parseInt($("#created-type").val()) || 0;
            var access_type = parseInt($("#access-type").val()) || 0;

            if (created_type != 1) {
                if ($('#created input').val().trim() == '') {
                    alert("登録日時設定 (日付 / 日) not empty !");
                    return false;
                }
            }

            if (access_type != 1) {
                if ($('#access input').val().trim() == '') {
                    alert("最終アクセス (日付 / 日) not empty !");
                    return false;
                }
            }

            return true;
        });
    });
</script>