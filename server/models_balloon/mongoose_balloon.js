var mongoose = require('mongoose');
var APP_CONFIG = require('../config/balloon.json');
var uris = APP_CONFIG.DB.DRIVER + "://" + APP_CONFIG.DB.HOST + ":" + APP_CONFIG.DB.PORT + "/" + APP_CONFIG.DB.DBNAME;
mongoose.connect(uris, function (err) {
    if  (err) {
        console.log("Cannt connect to DB");
        process.exit(1);
    }
});
module.exports = exports = mongoose;