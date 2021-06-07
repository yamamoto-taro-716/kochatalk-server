var mongoose = require('./mongoose_balloon');
var Schema = mongoose.Schema;

var messageSchema = new Schema({
    room_id: {
        type: String,
        index: true
    },
    send_id: Number,
    receive_id: Number,
    type: Number,
    message: {
        type: String,
        default: ""
    },
    is_read: {
        type: Number,
        default: 0
    },
    user_small_id_deleted: {
        type: Number,
        default: 0
    },
    user_big_id_deleted: {
        type: Number,
        default: 0
    },
    created: {type: Date, default: Date.now},
});
var messages = mongoose.model('messages', messageSchema);
module.exports = messages;