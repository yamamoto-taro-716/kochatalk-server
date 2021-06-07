<?php
/* @var \App\View\AppView $this */
/* @var \App\Model\Entity\Account $account */
$this->assign("title", __("ユーザー情報"));
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
                        <?= $this->Form->create($account) ?>
                        <div class="form-group">
                            <label for=""><?= __("会員ID") ?></label>
                            <p><?= $account->id ?></p>
                        </div>
                        <div class="form-group">
                            <label for=""><?= __("ニックネーム") ?></label>
                            <p><?= $account->nickname ?></p>
                        </div>
                        <div class="form-group">
                            <label for="">国籍</label>
                            <p><?= $this->Html->image('flags/flag_' . $account->nationality . '.png', ['width' => 25]) ?> <?= \App\Utility\AppUtil::getCountries()[$account->nationality] ?></p>
                        </div>
                        <div class="form-group">
                            <label><?= __("性別") ?></label>
                            <p><?= \App\Model\Entity\Account::getGenders()[$account->gender] ?></p>
                        </div>
                        <div class="form-group">
                            <label><?= __("会員ステータス") ?></label>
                            <p><?= \App\Model\Entity\Account::getStatus()[$account->status] ?></p>
                        </div>
                        <div class="form-group">
                            <label><?= __("登録日時") ?></label>
                            <p><?= $account->created->timezone("Asia/Tokyo")->format("Y-m-d H:i:s") ?></p>
                        </div>
                        <div class="form-group">
                            <label>OS</label>
                            <p><?= $account->device->user_agent ?></p>
                        </div>
                        <div class="form-group">
                            <label><?= __("最終ログイン日時") ?></label>
                            <p><?= $account->device->last_access->timezone("Asia/Tokyo")->format("Y-m-d H:i:s") ?></p>
                        </div>
                        <div class="form-group">
                            <label><?= __("Memo") ?></label>
                            <?= $this->Form->control("memo", ["label" => false, "class" => "form-control"]); ?>
                        </div>
                        <div class="form-group">
                            <label><?= __("ステータス") ?></label>
                            <?= $this->Form->control("status", ["label" => false, "type" => "select", "options" => \App\Model\Entity\Account::getStatus(), "class" => "form-control"]); ?>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Save</button>
                            <div class="pull-right">
		                        <?= $this->Html->link('Delete', ['action' => 'delete', $account->id], ['class' => 'btn btn-danger', 'confirm' => 'Are you sure ?']) ?>
                            </div>
                        </div>
                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-sm-9 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><?= __("メッセージ") ?></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="table-responsive">
                            <table id="tblMessage" class="table table-striped jambo_table bulk_action">
                                <thead>
                                <tr class="headings">
                                    <th class="column-title"><?= __("送信日時") ?></th>
                                    <th class="column-title"><?= __("ニックネーム") ?></th>
                                    <th class="column-title"><?= __("メッセージ") ?></th>
                                    <th class="column-title"><?= __("Receiver") ?></th>
                                    <th class="column-title"><?= __("#Action") ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <template>
                                    <tr>
                                        <td>{{created}}</td>
                                        <td>
                                            <a href="/accounts/view/{{account_id}}">{{nickname}}</a>
                                        </td>
                                        <td style="width: 30%">{{message}}</td>
                                        <td>
                                            <a href="/accounts/view/{{receive_id}}">{{receiver}}</a>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)" class="btn btn-primary btn-xs" onclick="openConversation(this, '{{room_id}}', '{{account_id}}', '{{nickname}}', '{{receiver}}')"><i class="fa fa-eye"></i></a>
                                            <a href="javascript:void(0)" class="btn btn-danger btn-xs" onclick="delMessage(this, '{{id}}')"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                </template>
                            </table>
                        </div>
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
    var BASE_URL_TIMELINE = "<?= $this->Url->build(["controller" => "Message", "action" => "getMessages"]) ?>";
    var NEXT_PAGE = "";
    var conditions = {
        id: <?= $account->id ?>
    };

    $(function () {
        loadData(BASE_URL_TIMELINE);

        $("#btnViewMore").click(function(){
            var next_page = BASE_URL_TIMELINE + "?" + NEXT_PAGE;
            loadData(next_page);
        });

        $("#frmFilter").on("submit", function () {
            filterMessage();
            return false;
        });

        $('.message-box').scroll(function() {
            var pos = $(this).scrollTop();
            if (pos == 0) {
                if (NEXT_PAGE_HISTORY != '') {
                    var next_page = BASE_URL_HISTORY + "?" + NEXT_PAGE_HISTORY;
                    loadDataConversation(next_page, 'top');
                }
            }
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
                moment.locale('ja');
                data.messages.forEach(function(item, index){
                    var message = "";
                    if (item.type == 1) {
                        message = item.message;
                    } else {
                        message = '<img src="' + item.message + '" style="width: 200px" />';
                    }
                    var html = $("#tblMessage template").html();
                    html = html.replace(new RegExp("{{created}}", "g"), moment.utc(item.created).tz('Asia/Tokyo').format('LLL:ss'))
                        .replace(new RegExp("{{nickname}}", "g"), item.nickname)
                        .replace(new RegExp("{{receiver}}", "g"), item.receiver)
                        .replace(new RegExp("{{nationality}}", "g"), item.nationality)
                        .replace(new RegExp("{{nationality_receive}}", "g"), item.nationality_receive)
                        .replace(new RegExp("{{account_id}}", "g"), item.account_id)
                        .replace(new RegExp("{{receive_id}}", "g"), item.receive_id)
                        .replace(new RegExp("{{id}}", "g"), item.id)
                        .replace(new RegExp("{{room_id}}", "g"), item.room_id)
                        .replace(new RegExp("{{message}}", "g"), message);
                    $("#tblMessage tbody").append(html);
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

    function filterMessage() {
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
        $("#tblMessage tbody").html('');
        loadData(BASE_URL_TIMELINE);
    }

    function reloadData() {
        $("#frmFilter")[0].reset();
        conditions = {};
        $("#tblMessage tbody").html('');
        loadData(BASE_URL_TIMELINE);
    }


    function delMessage(obj, id) {
        if (confirm('<?= __("Are you sure ?") ?>')) {
            $(obj).html("Deleting ...");
            $.ajax({
                url: '<?= $this->Url->build(["controller" => "Message", "action" => "delete"]) ?>',
                type: 'GET',
                data: {id: id},
                success: function (data) {
                    if  (data.status) {
                        $(obj).parent().parent().remove();
                    } else {
                        $(obj).html('<i class="fa fa-trash"></i>');
                    }
                },
                error: function (error) {
                    $(obj).html('<i class="fa fa-trash"></i>');
                }
            })
        }
    }

    var BASE_URL_HISTORY = "<?= $this->Url->build(["controller" => "Message", "action" => "getMessagesHistory"]) ?>";
    var NEXT_PAGE_HISTORY = "";
    var DATA_MESSAGES_HISTORY = {};

    function openConversation(obj, room_id, send_id, nickname, receiver) {
        DATA_MESSAGES_HISTORY.room_id = room_id;
        DATA_MESSAGES_HISTORY.send_id = parseInt(send_id) || 0;
        DATA_MESSAGES_HISTORY.nickname = nickname;
        DATA_MESSAGES_HISTORY.receiver = receiver;
        $(".message-box").html('');
        NEXT_PAGE_HISTORY = '';
        loadDataConversation(BASE_URL_HISTORY, 'bottom');
        $("#showConversationModal").modal('show');
    }

    function loadDataConversation(url, dir){
        $("#btnLoadingHistory").show();
        var send_id = DATA_MESSAGES_HISTORY.send_id;
        var nickname = DATA_MESSAGES_HISTORY.nickname;
        var receiver = DATA_MESSAGES_HISTORY.receiver;
        var room_id = DATA_MESSAGES_HISTORY.room_id;
        $.ajax({
            url: url,
            type: "GET",
            data: {room_id: room_id},
            success: function (json) {
                json.messages.forEach(function(data, index){
                    var html = $("template#message").html();
                    var chatbox = $(".message-box");
                    moment.locale('ja');
                    var message = "";
                    if (data.type == 1) {
                        message = data.message;
                    } else {
                        message = '<img src="' + data.message + '" style="width: 200px" />';
                    }
                    var dateCreated = moment.utc(data.created).tz('Asia/Tokyo').format('LLL:ss');
                    html = html.replace(new RegExp('_USER', 'g'), (send_id == data.send_id ? nickname : receiver))
                        .replace(new RegExp('_CREATED', 'g'), dateCreated)
                        .replace(new RegExp('_MESSAGE', 'g'), message)
                        .replace(new RegExp('{{id}}', 'g'), data.id);
                    if (send_id == data.send_id) {
                        html = html.replace('by-me', '');
                    }
                    chatbox.prepend(html);
                    if (dir == 'bottom') {
                        chatbox[0].scrollTop = chatbox[0].scrollHeight;
                    } else {
                        chatbox[0].scrollTop = 500;
                    }
                });
                NEXT_PAGE_HISTORY = json.next_page;
                $("#btnLoadingHistory").hide();
            },
            error: function (error) {
                $("#btnLoadingHistory").hide();
            }
        })
    }
</script>

<div class="modal fade" id="showConversationModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Conversation</h4>
            </div>
            <div class="modal-body">
                <div class="text-center" style="margin-top: 15px">
                    <p id="btnLoadingHistory"><i class="fa fa-spinner fa-spin"></i> Loading</p>
                </div>
                <div class="message-box">

                </div>
                <template id="message">
                    <div class="message-group by-me">
                        <h5>#_USER
                            <small>at _CREATED</small>
                            <a href="javascript:void(0)" class="text-danger" onclick="delMessage(this, '{{id}}')"><i class="fa fa-trash"></i></a>
                        </h5>
                        <p>_MESSAGE</p>
                    </div>
                </template>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
