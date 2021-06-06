<?php
/* @var \App\View\AppView $this */
$this->assign("title", __("問い合わせ"));
?>
<style>
    .checkbox {
        margin-top: 0;
    }
    .chatbox {
        height: 500px;
        overflow-y: auto;
    }
    .message-group {
        border-bottom: 1px solid #d5d5d5;
    }
    .message-group.by-me {
        text-align: right;
    }
    .message-group h5 {
        margin-top: 10px;
        margin-bottom: 2px;
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
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><?= __("ユーザー情報") ?></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="form-group">
                            <label for=""><?= __("会員ID") ?></label>
                            <p><?= $account->id ?></p>
                        </div>
                        <div class="form-group">
                            <label for=""><?= __("ニックネーム") ?></label>
                            <p><?= $account->nickname ?></p>
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
                            <div class="row">
                                <div class="col-xs-12">
                                    <a href="<?= $this->Url->build(["action" => "index"]) ?>" class="btn btn-block btn-default">
                                        <i class="fa fa-arrow-left"></i> Back
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-sm-8 col-xs-12">
                <div class="panel panel-primary">
                    <div class="panel-heading"><h3 class="panel-title">メッセージ</h3></div>
                    <div class="text-center" style="margin-top: 15px">
                        <p id="btnLoading"><i class="fa fa-spinner fa-spin"></i> Loading</p>
                    </div>
                    <div class="panel-body chatbox">
                        <template id="message">
                            <div class="message-group by-me">
                                <h5>#_USER
                                    <small>at _CREATED</small>
                                </h5>
                                <p>_MESSAGE</p>
                            </div>
                        </template>
                    </div>
                    <div class="panel-footer">
                        <div class="input-group">
                            <input id="txtMessage" type="text" placeholder="Type a message ..." class="form-control">
                            <span class="input-group-btn">
                                <button id="btnSend" type="button" class="btn btn-default">
                                    <span class="glyphicon glyphicon-send"></span>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->Html->script(\Cake\Core\Configure::read("App.socketBaseUrl") . "/socket.io/socket.io.js") ?>
<script>
    var BASE_URL_HISTORY = "<?= $this->Url->build(["action" => "getContactHistory"]) ?>";
    var NEXT_PAGE = "";
    var account_id = <?= $account->id ?>;

    $(function () {
        loadData(BASE_URL_HISTORY, 'bottom');

        var chatio = io('<?= \Cake\Core\Configure::read("App.socketBaseUrl") ?>/chat-balloon');
        var token = "<?= $jwt_token ?>";
        chatio.emit('authenticate', {
            token: token
        });

        joinRoom(account_id);

        function joinRoom(id) {
            chatio.emit('join:contact', {account_id: id});
        }

        chatio.on('response:contact', function(data){
            if (!data.status) {
                alert("Cannt connect to user !");
                return window.location.href = '<?= $this->Url->build(["action" => "index"]); ?>'
            }
        });

        function sendContact() {
            var msg = $('#txtMessage').val();
            chatio.emit('send:contact', {account_id: account_id, message: msg});
            $("#txtMessage").val('');
        }
        chatio.on('receive:contact', function(data){
            if (!data.status) {
                alert("Cannt connect to user !");
            }
            var html = $("template#message").html();
            var chatbox = $(".panel-body.chatbox");
            moment.locale('ja');
            var dateCreated = moment.utc(data.created).tz('Asia/Tokyo').format('LLL');
            html = html.replace(new RegExp('_USER', 'g'), (account_id == data.send_id ? '<?= $account->nickname ?>' : 'Admin'))
                .replace(new RegExp('_CREATED', 'g'), dateCreated)
                .replace(new RegExp('_MESSAGE', 'g'), data.message);
            if (account_id == data.send_id) {
                html = html.replace('by-me', '');
            }
            chatbox.append(html);
            chatbox[0].scrollTop = chatbox[0].scrollHeight
        });

        $('#txtMessage').on('keydown', function (event) {
            if (event.keyCode == 13) {
                sendContact();
            }
        });

        $('#btnSend').on('click', function (event) {
            sendContact();
        });

        $('.panel-body.chatbox').scroll(function() {
            var pos = $('.panel-body.chatbox').scrollTop();
            if (pos == 0) {
                if (NEXT_PAGE != '') {
                    var next_page = BASE_URL_HISTORY + "?" + NEXT_PAGE;
                    loadData(next_page, 'top');
                }
            }
        });
    });

    function loadData(url, dir){
        $("#btnLoading").show();
        $.ajax({
            url: url,
            type: "GET",
            data: {account_id: account_id},
            success: function (json) {
                json.messages.forEach(function(data, index){
                    var html = $("template#message").html();
                    var chatbox = $(".panel-body.chatbox");
                    moment.locale('ja');
                    var dateCreated = moment.utc(data.created).tz('Asia/Tokyo').format('LLL:ss');
                    html = html.replace(new RegExp('_USER', 'g'), (account_id == data.send_id ? '<?= $account->nickname ?>' : 'Admin'))
                        .replace(new RegExp('_CREATED', 'g'), dateCreated)
                        .replace(new RegExp('_MESSAGE', 'g'), data.message);
                    if (account_id == data.send_id) {
                        html = html.replace('by-me', '');
                    }
                    chatbox.prepend(html);
                    if (dir == 'bottom') {
                        chatbox[0].scrollTop = chatbox[0].scrollHeight;
                    } else {
                        chatbox[0].scrollTop = 500;
                    }
                });
                NEXT_PAGE = json.next_page;
                $("#btnLoading").hide();
            },
            error: function (error) {
                $("#btnLoading").hide();
            }
        })
    }
</script>