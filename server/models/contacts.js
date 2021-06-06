var mongoose = require('mongoose');
var Schema = mongoose.Schema;

var contactSchema = new Schema({
    account_id: {
        type: Number,
        index: true
    },
    nickname: String,
    message: {
        type: String,
        default: ""
    },
    is_reply: {
        type: Number,
        default: 0
    },
    created: {type: Date, default: Date.now},
});
var contacts = mongoose.model('contacts', contactSchema);
module.exports = contacts;