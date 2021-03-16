define([
    './model',
    './block'
], function (Model, Block) {
    return Model.extend({
        initConfig: function (data) {
            this._super();
            
            var blocks = [];
            _.each(data.blocks, function (itm) {
                var block = new Block(itm);
                block.subscribe(this.notify);
                
                blocks.push(block);
            }.bind(this));
            
            this.set('blocks', blocks);
            
            this.apply();
        }
    });
});
