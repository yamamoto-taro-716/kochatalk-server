<?php
/* @var \App\View\AppView $this */
$this->assign("title", __("プッシュ通知"));
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
                        <h2><?= __("ユーザー情報") ?></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form id="frmFilter" action="#">
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
                            <button type="submit" class="btn btn-info"><?= __("抽出") ?></button>
                            <button type="button" class="btn btn-default" onclick="reloadData()"><i class="fa fa-refresh"></i> <?= __("Refresh") ?></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-sm-9 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><?= __("Total device found") ?>: <span id="spNumber"><?= $device ?></span></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form id="frmSend" action="#">
                            <div class="form-group">
                                <?= $this->Form->control("title", ["label" => __("Title"), "class" => "form-control"]) ?>
                            </div>
                            <div class="form-group">
                                <?= $this->Form->control("content", ["type" => "textarea", "label" => __("Content"), "class" => "form-control", "rows" => 15]) ?>
                            </div>
                            <button id="btnSend" type="submit" class="btn btn-success"><?= __("Send") ?></button>
                            <span id="btnLoading" style="display: none"><i class="fa fa-spinner fa-spin"></i> sending</span>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var conditions = {};

    $(function () {
        $("#frmFilter").on("submit", function () {
            filter();
            return false;
        })
        $("#frmSend").on("submit", function () {
            sendPush();
            return false;
        })
    });

    function loadData(){
        if (conditions.action == "send") {
            $("#btnLoading").show();
        } else {
            $("#spNumber").text("Finding ...");
        }
        $("#btnSend").prop("disabled", true);

        $.ajax({
            url: "<?= $this->Url->build(["action" => "sendPush"]) ?>",
            type: "GET",
            data: conditions,
            success: function (data) {
                console.log(data);
                if (data.action == "search") {
                    $("#spNumber").text(data.number);
                } else {
                    if (data.status) {
                        new PNotify({
                            title: 'Information',
                            text: 'Success',
                            type: 'success',
                            styling: 'bootstrap3'
                        });
                    } else {
                        new PNotify({
                            title: 'Error',
                            text: 'Failure',
                            type: 'error',
                            styling: 'bootstrap3'
                        });
                    }
                }
                $("#btnLoading").hide();
                $("#btnSend").prop("disabled", false);
            },
            error: function (error) {
                $("#btnLoading").hide();
                $("#btnSend").prop("disabled", false);
            }
        })
    }

    function filter() {
        conditions.id = $("#id").val();
        conditions.nickname = $("#nickname").val();
        conditions.gender = [];
        $.each($("input[name='gender[]']:checked"), function (index, item){
            conditions.gender.push($(item).val());
        });
        conditions.os = [];
        $.each($("input[name='os[]']:checked"), function (index, item){
            conditions.os.push($(item).val());
        });
        conditions.status = [];
        $.each($("input[name='status[]']:checked"), function (index, item){
            conditions.status.push($(item).val());
        });
        conditions.action = "search";
        loadData();
    }

    function sendPush() {
        conditions.action = "send";
        conditions.title = $("#title").val();
        conditions.content = $("#content").val();
        if ($("#title").val() == '') {
            new PNotify({
                title: 'Error',
                text: 'Title must be input !',
                type: 'error',
                styling: 'bootstrap3'
            });
            return false;
        }
        if ($("#content").val() == '') {
            new PNotify({
                title: 'Error',
                text: 'Content must be input !',
                type: 'error',
                styling: 'bootstrap3'
            });
            return false;
        }
        loadData();
    }

    function reloadData() {
        location.reload();
    }
</script>