var mongoose = require('./mongoose_balloon');
var Schema = mongoose.Schema;

var accountSchema = new Schema({
    account_id: {
        type: Number,
        index: true
    },
    nickname: String,
    nationality: String,
    gender: Number,
    avatar: String,
    revision: Number,
    user_agent: {
        type: String,
        index: true,
        default: ""
    },
    push_token: {
        type: String,
        default: ""
    },
    status: Number,
    created: {type: Date, default: Date.now},
});
var accounts = mongoose.model('accounts', accountSchema);
module.exports = accounts;