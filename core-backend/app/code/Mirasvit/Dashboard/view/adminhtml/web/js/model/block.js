define([
    './model'
], function (Model) {
    return Model.extend({
        asArray: function () {
            return this._asArray(this.data(), ['value', 'error']);
        }
    });
});
