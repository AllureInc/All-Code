define([
    'underscore',
    'uiElement',
    './model/board'
], function (_, Element, Board) {
    'use strict';
    
    return Element.extend({
        deleteBoardUrl: null,
        saveBoardUrl:   null,
        board:          null,
        boards:         [],
        isChanged:      false,
        
        defaults: {
            listens: {
                boards: 'updateBoard'
            },
            
            tracks: {
                //isLoading:  true,
                //params: true,
                isChanged: true,
                boards:    true
                //board:      true
            }
        },
        
        initialize: function () {
            this._super();
            
            this.set('params', {});
            
            var boards = [];
            _.each(this.data['boards'], function (board) {
                var model = new Board(board);
                boards.push(model);
            }.bind(this));
            this.set('boards', boards);
        },
        
        updateBoard: function () {
            var board;
            
            var boardId = this.board ? this.board.get('board_id') : this.data['defaultBoardId'];
            
            board = _.find(this.boards, function (itm) {
                return itm.get('board_id') === boardId;
            });
            
            // board was removed or not set yet
            if (!board) {
                board = _.first(this.boards);
                if (!board) {
                    board = new Board({identifier: 'dashboard', title: 'Dashboard', blocks: []});
                }
            }

            if (!this.board || this.board.get('board_id') !== board.get('board_id')) {
                this.set('board', board);
            }
        }
    });
});
