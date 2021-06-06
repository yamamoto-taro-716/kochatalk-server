var APP_CONFIG = require('./config/common.json');
var APP = require('./app');
var _ = require('underscore');
var socketioJwt = require('socketio-jwt');
var moment = require('moment');
var momentz = require('moment-timezone');

/* MODEL */
var accountModel = require('./models/accounts');
var messageModel = require('./models/messages');
var contactModel = require('./models/contacts');
var contactMessageModel = require('./models/contact_messages');
/* END - MODEL */

var socket_chat_ns = '/chat';

function getSocketsInRoom(io, room, namespace = '/') {
    let _room = io.nsps[namespace].adapter.rooms[room];
    if (_room) {
        return Object.keys(_room.sockets);
    } else {
        return [];
    }
}

function genRoomID(user_id, friend_id) {
    return '#' + (user_id < friend_id ? user_id + '-' + friend_id : friend_id + '-' + user_id);
}

module.exports = function (io) {
    var users = [];
    var chatio = io.of(socket_chat_ns);
    // var chatio = io;

    chatio.on('connection', socketioJwt.authorize({
        secret: APP_CONFIG.APP.SALT_KEY,
        callback: false,
        algorithm: 'HS256'
    }));

    chatio.on('authenticated', function (socket) {
        
        //Get user info from JWT
        var user_info = socket.decoded_token.profile;
        socket.decoded_token.socket_id = socket.id;
        users.push(socket.decoded_token);

        let accModel = {
            account_id: user_info.id,
            nickname: user_info.nickname,
            nationality: user_info.nationality,
            gender: user_info.gender,
            avatar: user_info.avatar,
            revision: user_info.revision,
            user_agent: user_info.user_agent,
            push_token: user_info.push_token,
            status: user_info.status
        };

        accountModel.findOne({
            account_id: user_info.id
        }, function (err, account) {
            if (!account) {
                accountModel.create(accModel, function (err, account) {

                });
            } else if (user_info.revision > account.revision) {
                account.nickname = user_info.nickname;
                account.nationality = user_info.nationality;
                account.gender = user_info.gender;
                account.avatar = user_info.avatar;
                account.revision = user_info.revision;
                account.user_agent = user_info.user_agent;
                account.push_token = user_info.push_token;
                account.status = user_info.status;
                account.save();
            } else {
                account.user_agent = user_info.user_agent;
                account.push_token = user_info.push_token;
                account.save();
            }
        });

        console.log('user connected (id: ' + user_info.id + ')');

        socket.on('join:room', function (data) {
            //Init room_name
            var room_id = genRoomID(user_info.id, data.friend_id);
            //Get socket already in current room
            var sockets = getSocketsInRoom(io, room_id, socket_chat_ns);
            console.log('join:room ' + room_id + ' -- ' + user_info.id);
            socket.join(room_id);
            socket.emit('response:room', {
                status: true,
                action: room_id,
                msg: 'Success'
            });
        });

        socket.on('leave:room', function (data) {
            var room_id = genRoomID(user_info.id, data.friend_id);
            socket.leave(room_id);
            console.log('leave:room ' + room_id);
            socket.emit('response:room', {
                status: true,
                action: 'leave:room',
                msg: 'Success'
            });
        });

        socket.on('join:contact', function (data) {
            //Init room_name
            var room_id = genRoomID(user_info.id, data.account_id);
            //Get socket already in current room
            var sockets = getSocketsInRoom(io, room_id, socket_chat_ns);
            if (sockets.length == 2) {
                socket.emit('response:contact', {
                    status: false,
                    action: "join:contact",
                    msg: 'Full socket'
                });
            } else {
                let account_id = data.account_id <= 0 ? user_info.id : data.account_id;
                contactModel.findOne({
                    'account_id': account_id
                }, function (err, contact) {
                    if (err) {
                        console.log("find:contact:error");
                        return;
                    }
                    var nickname = "";
                    for (let i = 0; i < users.length; i++) {
                        if (users[i].profile.id == account_id) {
                            nickname = users[i].profile.nickname;
                            break;
                        }
                    }
                    if (contact) {
                        socket.join(room_id);
                        socket.emit('response:contact', {
                            status: true,
                            action: "join:contact",
                            msg: 'Success'
                        });
                        if  (data.account_id <= 0){
                            contact.nickname = user_info.nickname;
                            contact.save();
                        }

                        console.log("join:contact OK: " + room_id);
                    } else {
                        var model = {
                            account_id: account_id,
                            nickname: nickname,
                            message: "",
                            is_reply: -1
                        };
                        contactModel.create(model, function (err, contactSave) {
                            if (err) {
                                socket.emit('response:contact', {
                                    status: false,
                                    action: "join:contact",
                                    msg: 'Cannt create contact'
                                });
                                console.log("join:contact Failed" + room_id);
                            } else {
                                socket.join(room_id);
                                socket.emit('response:contact', {
                                    status: true,
                                    action: "join:contact",
                                    msg: 'Success'
                                });
                                console.log("join:contact OK" + room_id);
                            }
                        });
                    }
                });
            }
        });

        socket.on('leave:contact', function (data) {
            var room_id = genRoomID(user_info.id, data.account_id);
            socket.leave(room_id);
            console.log('leave:contact ' + room_id);
            socket.emit('response:contact', {
                status: true,
                action: 'leave:contact' + room_id,
                msg: 'Success'
            });
        });

        socket.on('send:contact', function (data) {
            var room_id = genRoomID(user_info.id, data.account_id);
            let rooms = io.nsps["/chat"].adapter.rooms;
            var hasRoom = false;
            _.each(rooms, function (room, index) {
                if (index == room_id) {
                    hasRoom = true;
                }
            });
            if (!hasRoom) {
                console.log("contact:error:noRoom");
                return;
            }
            let account_id = (data.account_id <= 0 ? user_info.id : data.account_id);
            var messages = {
                account_id: account_id,
                send_id: user_info.id,
                receive_id: data.account_id,
                message: data.message
            };
            var dataReturn = {
                status: true,
                id: '',
                send_id: user_info.id,
                receive_id: data.account_id,
                message: data.message,
                created: momentz(new Date()).tz('UTC').format('YYYY-MM-DD HH:mm:ss')
            };
            var room_sockets = getSocketsInRoom(io, room_id, socket_chat_ns);
            if (room_sockets.length < 2) {
                messages.is_read = 0;
            } else {
                messages.is_read = 1;
            }
            
            contactMessageModel.create(messages, function (err, message) {
                if (err) {
                    dataReturn.status = false;
                    console.log('EMIT: send:contact:error');
                } else {
                    dataReturn.id = message.id;
                    if (messages.is_read == 0) {
                        if (user_info.id <= 0) {
                            var isOnline = false;
                            var accountSocketId = '';
                            for (var i = 0; i < users.length; i++) {
                                if (users[i].profile.id == data.account_id) {
                                    isOnline = true;
                                    accountSocketId = users[i].socket_id;
                                }
                            }
                            if (isOnline) {
                                socket.to(accountSocketId).emit('push:notification', {
                                    id: user_info.id,
                                    nickname: '',
                                    message: data.message,
                                    gender: 0,
                                    avatar: '',
                                    nationality: '',
                                    revision: 0,
                                    created: dataReturn.created
                                });
                                console.log("send:contact:notification");
                            } else {
                                //TODO: Push notification
                                console.log("send:contact:push");
                                accountModel.findOne({
                                    account_id: data.account_id
                                }, function (err, account) {
                                    if (account && account.push_token) {
                                        if (account.user_agent == 'android') {
                                            let admin = {
                                                title: '',
                                                id: user_info.id,
                                                nationality: '',
                                                gender: 0,
                                                avatar: '',
                                                revision: 0,
                                            };
                                            APP.pushAndroid(account.push_token, {content: data.message, created: dataReturn.created}, admin);
                                        }
                                    }
                                });
                            }
                        }
                    }
                }
                contactModel.findOne({
                    account_id: account_id
                }, function (err, contact) {
                    if (contact) {
                        if (account_id == user_info.id) {
                            contact.is_reply = 0;
                        } else {
                            contact.is_reply = 1;
                        }
                        contact.message = data.message;
                        contact.created = Date.now();
                        contact.save();
                    }
                });
                chatio.to(room_id).emit('receive:contact', dataReturn);
                console.log("send:contact:" + room_id);
            });
        });

        socket.on('send:message', function (data) {
            var room_id = genRoomID(user_info.id, data.friend_id);
            let rooms = io.nsps["/chat"].adapter.rooms;
            var hasRoom = false;
            _.each(rooms, function (room, index) {
                if (index == room_id) {
                    hasRoom = true;
                }
            });
            if (!hasRoom) {
                console.log("error:noRoom");
                return;
            }
            var messages = {
                room_id: room_id,
                send_id: user_info.id,
                receive_id: data.friend_id,
                type: data.type,
                message: data.message,
                is_read: 1,
                user_small_id_deleted: 0,
                user_big_id_deleted: 0
            };
            var dataReturn = {
                status: true,
                id: '',
                send_id: user_info.id,
                receive_id: data.friend_id,
                message: data.message,
                type: data.type,
                created: momentz(new Date()).tz('UTC').format('YYYY-MM-DD HH:mm:ss')
            };
            var room_sockets = getSocketsInRoom(io, room_id, socket_chat_ns);
            if (room_sockets.length < 2) {
                messages.is_read = 0;
            } else {
                messages.is_read = 1;
            }
            messageModel.create(messages, function (err, message) {
                if (err) {
                    dataReturn.status = false;
                    console.log('EMIT: send:message:error');
                } else {
                    dataReturn.id = message.id;
                    if (message.is_read == 0) {
                        var isOnline = false;
                        var accountSocketId = '';
                        for (var i = 0; i < users.length; i++) {
                            if (users[i].profile.id == data.friend_id) {
                                isOnline = true;
                                accountSocketId = users[i].socket_id;
                            }
                        }
                        if (isOnline) {
                            socket.to(accountSocketId).emit('push:notification', {
                                id: user_info.id,
                                nickname: user_info.nickname,
                                message: data.message,
                                gender: user_info.gender,
                                avatar: user_info.avatar,
                                nationality: user_info.nationality,
                                revision: user_info.revision,
                                created: dataReturn.created
                            });
                            console.log("send:message:notification");
                        } else {
                            //TODO: Push notification
                            console.log("send:message:push");
                            accountModel.findOne({
                                account_id: data.friend_id
                            }, function (err, account) {
                                if (account && account.push_token) {
                                    if (account.user_agent == 'android') {
                                        APP.pushAndroid(account.push_token, {content: data.message, created: dataReturn.created}, user_info);
                                    }
                                }
                            });

                        }
                    }
                }
                chatio.to(room_id).emit('receive:message', dataReturn);
            });
        });

        socket.on('send:action', function (data) {
            var room_id = genRoomID(user_info.id, data.friend_id);
            let rooms = io.nsps["/chat"].adapter.rooms;
            var hasRoom = false;
            _.each(rooms, function (room, index) {
                if (index == room_id) {
                    hasRoom = true;
                }
            });
            if (!hasRoom) {
                console.log("error:noRoom");
                return;
            } 
            var dataReturn = {
                status: true,
                id: data.action,
                send_id: user_info.id,
                receive_id: data.friend_id,
                message: '',
                type: 1,
                created: momentz(new Date()).tz('UTC').format('YYYY-MM-DD HH:mm:ss')
            };
            chatio.to(room_id).emit('receive:message', dataReturn);
        });


        socket.on('disconnect', function (reason) {
            console.log(reason + ' User: ' + socket.decoded_token.sub);
            for (var i = 0; i < users.length; i++) {
                if (users[i].sub == socket.decoded_token.sub) {
                    users.splice(i, 1);
                }
            }
        });
    });
}