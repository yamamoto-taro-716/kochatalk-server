$(function () {
    var chatio = io('/chat');
    var token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsInByb2ZpbGUiOnsiaWQiOjQsIm5pY2tuYW1lIjoiY2huZyIsIm5hdGlvbmFsaXR5IjoiSlAiLCJnZW5kZXIiOjEsImF2YXRhciI6IiIsInJldmlzaW9uIjoxLCJzdGF0dXMiOjEsInVzZXJfYWdlbnQiOiJpb3MiLCJwdXNoX3Rva2VuIjpudWxsfX0.2Vbx1RL4quSreznGVcDtcecpXQXg1l2Jg2iQ3JK1_NU";
    chatio.emit('authenticate', {
        token: token
    });

    window.onload = function () {
        // switchRoom(user_info.friend_id);
    }

    chatio.on('response:room', function (data) {
        console.log(data)
        if  (data.status) {
            $("#list-events").append('<p>[on] response:room ' + data.action + ' SUCCESS</p>');
        } else {
            $("#list-events").append('<p>[on] response:room ' + data.action + ' FAILED</p>');
        }
        
    });

    chatio.on('update:users', function (data) {
        var html = '';
        for (var i = 0; i < data.length; i++) {
            var element = data[i];
            html += '<li class="list-group-item">' + element.email + '</li>';
        }
        $("#list-users-online").html(html);
        $("#list-events").append('<p>[on] update:users</p>');
    });

    chatio.on('receive:message', function (data) {
        addMessage(data);
        console.log(data)
        $("#list-events").append('<p>[on] receive:message</p>');
    });

    chatio.on("unauthorized", function (error) {
        if (error.data.type == "UnauthorizedError" || error.data.code == "invalid_token") {
            console.log("User's token has expired");
        }
    });

    $('#txtMessage').on('keydown', function (event) {
        if (event.keyCode == 13) {
            let message = $(this).val();
            let friend_id = parseInt($("#txtID").val()) || 0;
            sendMessage({type: 1, message: message, friend_id: friend_id});
            $(this).val('');
        }
    });

    function sendMessage(message) {
        chatio.emit('send:message', message);
        $("#list-events").append('<p>[emit] send:message</p>');
    }

    $("#btnJoinRoom").click(function(){
        switchRoom();
    });

    $("#btnLeaveRoom").click(function(){
        let friend_id = parseInt($("#txtID").val()) || 0;
        chatio.emit('leave:room', {friend_id: friend_id});
    });

    function switchRoom() {
        let friend_id = parseInt($("#txtID").val()) || 0;
        chatio.emit('join:room', {friend_id: friend_id});
        $("#list-events").append('<p>[emit] join:room</p>');
    }

    function addMessage(data) {
        let friend_id = parseInt($("#txtID").val()) || 0;
        var html = $("template#message").html();
        var chatbox = $(".panel-body.chatbox");
        html = html.replace(new RegExp('_USER', 'g'), (friend_id != data.send_id ? 'You' : data.send_id))
            .replace(new RegExp('_CREATED', 'g'), data.created)
            .replace(new RegExp('_MESSAGE', 'g'), data.message);
        if (friend_id != data.send_id) {
            html = html.replace('by-me', '');
        }
        chatbox.append(html);
        chatbox[0].scrollTop = chatbox[0].scrollHeight
    }

    function randomString(len) {
        var text = " ";

        var charset = "abcdefghijklmnopqrstuvwxyz0123456789";

        for (var i = 0; i < len; i++)
            text += charset.charAt(Math.floor(Math.random() * charset.length));

        return text.toUpperCase().trim();
    }

    /* CONTACT */

    $("#btnJoinContact").on('click', function(){
        chatio.emit('join:contact', {account_id: 0});
    });

    $("#btnSendContact").on('click', function(){
        let message = $("#txtMessage").val();
        chatio.emit('send:contact', {account_id: 0, message: message});
        $("#txtMessage").val('');
    });

    chatio.on('receive:contact', function (data) {
        addMessage(data);
        console.log(data)
        $("#list-events").append('<p>[on] receive:contact</p>');
    });
});