<?php
/* @var \App\View\AppView $this */
$this->assign("title", __("タイムライン管理"));
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
                            <button type="button" class="btn btn-default" onclick="reloadData()"><i class="fa fa-refresh"></i> <?= __("Refresh") ?></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-sm-9 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><?= __("タイムライン") ?></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <ul id="lstTimeline" class="messages">

                        </ul>
                        <template id="tplListPost">
                            <li>
                                <img src="{{avatar}}" class="avatar">
                                <div class="message_wrapper">
                                    <h4 class="heading">
                                        <a href="/accounts/view/{{account_id}}">{{nickname}}</a>
                                        <br>
                                        <small>{{date}}</small>
                                    </h4>
                                    <div class="row" style="margin: 10px 0">
                                        {{IMAGES}}
                                    </div>
                                    <blockquote class="message">{{content}}</blockquote>
                                    <br>
                                    <p class="url">
                                        <a href="javascript:void(0)" class="text-danger" onclick="delPost(this, {{id}})"><i class="fa fa-trash"></i> Delete</a>
                                    </p>
                                </div>
                            </li>
                        </template>
                        <div class="text-center">
                            <p id="btnLoading"><i class="fa fa-spinner fa-spin"></i> Loading</p>
                            <a id="btnViewMore" href="javascript:void(0)" class="btn btn-default btn-sm" style="display: none">View more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var BASE_URL_TIMELINE = "<?= $this->Url->build(["action" => "getTimeline"]) ?>";
    var NEXT_PAGE = "";
    var conditions = {};

    $(function () {
        loadData(BASE_URL_TIMELINE);

        $("#btnViewMore").click(function(){
            var next_page = BASE_URL_TIMELINE + "?" + NEXT_PAGE;
            loadData(next_page);
        });

        $("#frmFilter").on("submit", function () {
            filterPost();
            return false;
        });


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

    function loadData(url){
        $("#btnLoading").show();
        $("#btnViewMore").hide();
        $.ajax({
            url: url,
            type: "GET",
            data: conditions,
            success: function (data) {
                data.posts.forEach(function(item, index){
                    var images = "";
                    item.images.forEach(function(img, idx) {
                        images += '<div class="col-sm-3 thumbnail" style="background-image: url(' + img + '); background-size: cover; height: 120px; cursor: pointer" onclick="showImage(\''+ img +'\')"></div>';
                    });
                    var avatar = "/img/avatar.png";
                    var html = $("#tplListPost").html();
                    html = html.replace(new RegExp("{{date}}", "g"), item.date)
                        .replace(new RegExp("{{content}}", "g"), item.content)
                        .replace(new RegExp("{{id}}", "g"), item.id)
                        .replace(new RegExp("{{account_id}}", "g"), item.account_id)
                        .replace(new RegExp("{{nickname}}", "g"), item.nickname)
                        .replace(new RegExp("{{avatar}}", "g"), item.avatar ? item.avatar : avatar)
                        .replace(new RegExp("{{nationality}}", "g"), item.nationality)
                        .replace(new RegExp("{{IMAGES}}", "g"), images);
                    $("#lstTimeline").append(html);
                });
                NEXT_PAGE = data.next_page;
                if (data.next_page) {
                    $("#btnViewMore").show();
                } else {
                    $("#btnViewMore").hide();
                }
                $("#btnLoading").hide();
            },
            error: function (error) {
                $("#btnLoading").hide();
            }
        })
    }

    function filterPost() {
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
        conditions.created_from = $("#created-from").val();
        conditions.created_to = $("#created-to").val();
        conditions.login_from = $("#login-from").val();
        conditions.login_to = $("#login-to").val();

        $("#lstTimeline").html('');
        loadData(BASE_URL_TIMELINE);
    }

    function reloadData() {
        $("#frmFilter")[0].reset();
        conditions = {};
        $("#lstTimeline").html('');
        loadData(BASE_URL_TIMELINE);
    }


    function delPost(obj, id) {
        if (confirm('<?= __("Are you sure ?") ?>')) {
            $(obj).hide();
            $(obj).parent().append("Deleting ...");
            $.ajax({
                url: '<?= $this->Url->build(["controller" => "Accounts", "action" => "delPost"]) ?>',
                type: 'GET',
                data: {id: id},
                success: function (data) {
                    if  (data.status) {
                        $(obj).parent().parent().parent().remove();
                    } else {
                        $(obj).show();
                    }
                },
                error: function (error) {
                    $(obj).show();
                }
            })
        }
    }

    function showImage(img) {
        $("#imageView").attr("src", img);
        $("#showImageModal").modal('show');
    }
</script>
<div class="modal fade" id="showImageModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Image</h4>
            </div>
            <div class="modal-body">
                <img id="imageView" src="/" class="img-responsive center-block" alt="Image">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->