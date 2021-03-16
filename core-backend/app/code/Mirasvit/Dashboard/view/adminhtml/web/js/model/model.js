define([
    'uiClass',
    'underscore',
    'ko',
    'mageUtils'
], function (Class, _, ko, Utils) {
    return Class.extend({
        data:          {},
        origData:      {},
        subscriptions: [],
        
        initialize: function (data) {
            _.bindAll(this, 'isChanged');
            
            Utils.limit(this, 'notify', 50);
            
            this.data = ko.observable({});
            
            this.initConfig(data);
            
            return this;
        },
        
        initConfig: function (data) {
            this.set(data);
            this.apply();
        },
        
        apply: function () {
            this.origData = this.asArray();
        },
        
        get: function (key, placeholder) {
            var result = Utils.nested(this.data(), key);
            
            if (result === undefined && placeholder !== undefined) {
                return placeholder;
            }
            
            return result;
        },
        
        set: function (key, value) {
            if (value === undefined) {
                _.each(key, function (v, k) {
                    this.set(k, v);
                }.bind(this));
            } else {
                this.data()[key] = value;
            }
            
            this.notify();
        },
        
        isChanged: function () {
            return !_.isEqual(this.origData, this.asArray());
        },
        
        restore: function () {
            this.initConfig(this.origData)
        },
        
        asArray: function () {
            return this._asArray(this.data(), []);
        },
        
        _asArray: function (data, omit) {
            var result = {};
            
            _.each(_.omit(data, omit), function (value, key) {
                if (_.isObject(value)) {
                    if (_.isFunction(value.asArray)) {
                        result[key] = value.asArray()
                    } else if (!_.isFunction(value)) {
                        result[key] = this._asArray(value);
                    }
                } else if (_.isArray(value)) {
                    result[key] = this._asArray(value);
                } else {
                    result[key] = value;
                }
            }.bind(this));
            
            return result;
        },
        
        notify: function () {
            this.data.valueHasMutated();
        },
        
        subscribe: function (callback) {
            this.subscriptions.push(
                this.data.subscribe(callback)
            );
        },
        
        unsubscribe: function () {
            _.each(this.subscriptions, function (subscription) {
                subscription.dispose();
            });
        }
    });
});
