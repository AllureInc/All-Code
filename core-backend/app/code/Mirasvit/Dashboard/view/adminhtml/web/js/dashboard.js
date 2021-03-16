define([
    'underscore',
    'uiComponent',
    'uiLayout',
    'uiRegistry',
    './action/board/select',
    './action/board/delete',
    './action/board/add',
    './action/board/edit',
    './action/board/save',
    './action/board/refresh'
], function (_,
             Component,
             Layout,
             Registry,
             selectBoard,
             deleteBoard,
             addBoard,
             editBoard,
             saveBoard,
             refreshBoard) {
    'use strict';
    
    // we disabled mutation observers that used in ui/../lib/dom-observer.js for prevent slow rendering
    window.MutationObserver = function () {
        this.observe = function () {
        }
    };
    return Component.extend({
        boardId: null,
        
        defaults: {
            template: 'Mirasvit_Dashboard/dashboard',
            
            imports: {
                board: '${ $.provider }:board'
            },
            
            exports: {
                isChanged: '${ $.provider }:isChanged'
            },
            
            listens: {
                board: 'renderBoardView',
                
                '${ $.provider }:board.select':  'selectBoard',
                '${ $.provider }:board.delete':  'deleteBoard',
                '${ $.provider }:board.edit':    'editBoard',
                '${ $.provider }:board.add':     'addBoard',
                '${ $.provider }:board.save':    'saveBoard',
                '${ $.provider }:board.reset':   'resetBoard',
                '${ $.provider }:board.refresh': 'refreshBoard'
            }
        },
        
        initialize: function () {
            _.bindAll(this, 'onBoardChange');
            
            this._super();
        },
        
        renderBoardView: function () {
            if (this.boardId === this.board.get('board_id')) {
                return;
            }
            
            this.removeBoardView();
            
            this.boardId = this.board.get('board_id');
            
            this.board.subscribe(this.onBoardChange);
            
            Layout([{
                component: 'Mirasvit_Dashboard/js/view/board',
                provider:  this.provider,
                name:      this.board.get('board_id'),
                id:        'board',
                board_id:  this.board.get('board_id'),
                board:     this.board
            }], this);
        },
        
        removeBoardView: function () {
            this.boardId = null;
            var elem = _.findWhere(this.elems(), {
                id: 'board'
            });
            
            if (elem) {
                elem.destroy();
            }
        },
        
        onBoardChange: function () {
            this.set('isChanged', this.board.isChanged());
        },
        
        selectBoard: function (boardId) {
            selectBoard(this.provider, boardId);
        },
        
        addBoard: function () {
            addBoard(this.provider);
        },
        
        editBoard: function () {
            var board = this.getCurrentBoard();
            
            editBoard(this.provider, board);
        },
        
        deleteBoard: function () {
            var board = this.getCurrentBoard();
            
            deleteBoard(this.provider, board);
        },
        
        saveBoard: function () {
            var board = this.getCurrentBoard();
            
            saveBoard(this.provider, board);
        },
        
        refreshBoard: function () {
            var board = this.getCurrentBoard();
            
            refreshBoard(this.provider, board);
        },
        
        resetBoard: function () {
            var board = this.getCurrentBoard();
            this.removeBoardView();
            
            board.restore();
            
            this.renderBoardView();
        },
        
        getCurrentBoard: function () {
            var provider = Registry.get(this.provider);
            
            return _.find(provider.boards, function (itm) {
                return itm.get('board_id') === this.board.get('board_id');
            }.bind(this));
        }
    });
});
