var mongoose = require('mongoose');
var Schema = mongoose.Schema;

var contactMessageSchema = new Schema({
    account_id: {
        type: Number,
        index: true
    },
    send_id: Number,
    receive_id: Number,
    message: {
        type: String,
        default: ""
    },
    created: {type: Date, default: Date.now},
});
var contact_messages = mongoose.model('contact_messages', contactMessageSchema);
module.exports = contact_messages;