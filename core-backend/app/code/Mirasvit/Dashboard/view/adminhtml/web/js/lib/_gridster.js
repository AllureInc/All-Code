// @codingStandardsIgnoreFile
(function (root, factory) {
    
    if (typeof define === 'function' && define.amd) {
        define('gridster-coords', ['jquery'], factory);
    } else {
        root.GridsterCoords = factory(root.jQuery || root.jQuery);
    }
    
}(this, function (jQuery) {
    /**
     * Creates objects with coordinates (x1, y1, x2, y2, cx, cy, width, height)
     * to simulate DOM elements on the screen.
     * Coords is used by Gridster to create a faux grid with any DOM element can
     * collide.
     *
     * @class       Coords
     * @param       {HTMLElement|Object} obj The jQuery HTMLElement or a object with: left,
     * top, width and height properties.
     * @return      {Object} Coords instance.
     * @constructor
     */
    function Coords(obj) {
        if (obj[0] && jQuery.isPlainObject(obj[0])) {
            this.data = obj[0];
        } else {
            this.el = obj;
        }
        
        this.isCoords = true;
        this.coords = {};
        this.init();
        return this;
    }
    
    var fn = Coords.prototype;
    
    fn.init = function () {
        this.set();
        this.original_coords = this.get();
    };
    
    fn.set = function (update, not_update_offsets) {
        var el = this.el;
        
        if (el && !update) {
            this.data = el.offset();
            this.data.width = el.width();
            this.data.height = el.height();
        }
        
        if (el && update && !not_update_offsets) {
            var offset = el.offset();
            this.data.top = offset.top;
            this.data.left = offset.left;
        }
        
        var d = this.data;
        
        typeof d.left === 'undefined' && (d.left = d.x1);
        typeof d.top === 'undefined' && (d.top = d.y1);
        
        this.coords.x1 = d.left;
        this.coords.y1 = d.top;
        this.coords.x2 = d.left + d.width;
        this.coords.y2 = d.top + d.height;
        this.coords.cx = d.left + (d.width / 2);
        this.coords.cy = d.top + (d.height / 2);
        this.coords.width = d.width;
        this.coords.height = d.height;
        this.coords.el = el || false;
        
        return this;
    };
    
    fn.update = function (data) {
        if (!data && !this.el) {
            return this;
        }
        
        if (data) {
            var new_data = jQuery.extend({}, this.data, data);
            this.data = new_data;
            return this.set(true, true);
        }
        
        this.set(true);
        return this;
    };
    
    fn.get = function () {
        return this.coords;
    };
    
    fn.destroy = function () {
        this.el.removeData('coords');
        delete this.el;
    };
    
    //jQuery adapter
    jQuery.fn.coords = function () {
        if (this.data('coords')) {
            return this.data('coords');
        }
        
        var ins = new Coords(this, arguments[0]);
        this.data('coords', ins);
        return ins;
    };
    
    return Coords;
    
}));

;
(function (root, factory) {
    
    if (typeof define === 'function' && define.amd) {
        define('gridster-collision', ['jquery', 'gridster-coords'], factory);
    } else {
        root.GridsterCollision = factory(
            root.jQuery || root.jQuery,
            root.GridsterCoords
        );
    }
    
}(this, function (jQuery, Coords) {
    
    var defaults = {
        colliders_context:  document.body,
        overlapping_region: 'C'
        // ,on_overlap: function(collider_data){},
        // on_overlap_start : function(collider_data){},
        // on_overlap_stop : function(collider_data){}
    };
    
    /**
     * Detects collisions between a DOM element against other DOM elements or
     * Coords objects.
     *
     * @class       Collision
     * @uses        Coords
     * @param       {HTMLElement} el The jQuery wrapped HTMLElement.
     * @param       {HTMLElement|Array} colliders Can be a jQuery collection
     *  of HTMLElements or an Array of Coords instances.
     * @param       {Object} [options] An Object with all options you want to
     * @param       {String} [options.overlapping_region] Determines when collision
     *    is valid, depending on the overlapped area. Values can be: 'N', 'S',
     *    'W', 'E', 'C' or 'all'. Default is 'C'.
     * @param       {Function} [options.on_overlap_start] Executes a function the first
     *    time each `collider ` is overlapped.
     * @param       {Function} [options.on_overlap_stop] Executes a function when a
     *    `collider` is no longer collided.
     * @param       {Function} [options.on_overlap] Executes a function when the
     * mouse is moved during the collision.
     * @return      {Object} Collision instance.
     * @constructor
     */
    function Collision(el, colliders, options) {
        this.options = jQuery.extend(defaults, options);
        this.jQueryelement = el;
        this.last_colliders = [];
        this.last_colliders_coords = [];
        this.set_colliders(colliders);
        
        this.init();
    }
    
    Collision.defaults = defaults;
    
    var fn = Collision.prototype;
    
    fn.init = function () {
        this.find_collisions();
    };
    
    fn.overlaps = function (a, b) {
        var x = false;
        var y = false;
        
        if ((b.x1 >= a.x1 && b.x1 <= a.x2)
            || (b.x2 >= a.x1 && b.x2 <= a.x2)
            || (a.x1 >= b.x1 && a.x2 <= b.x2)
        ) {
            x = true;
        }
        
        if ((b.y1 >= a.y1 && b.y1 <= a.y2)
            || (b.y2 >= a.y1 && b.y2 <= a.y2)
            || (a.y1 >= b.y1 && a.y2 <= b.y2)
        ) {
            y = true;
        }
        
        return (x && y);
    };
    
    fn.detect_overlapping_region = function (a, b) {
        var regionX = '';
        var regionY = '';
        
        if (a.y1 > b.cy && a.y1 < b.y2) {
            regionX = 'N';
        }
        if (a.y2 > b.y1 && a.y2 < b.cy) {
            regionX = 'S';
        }
        if (a.x1 > b.cx && a.x1 < b.x2) {
            regionY = 'W';
        }
        if (a.x2 > b.x1 && a.x2 < b.cx) {
            regionY = 'E';
        }
        
        return (regionX + regionY) || 'C';
    };
    
    fn.calculate_overlapped_area_coords = function (a, b) {
        var x1 = Math.max(a.x1, b.x1);
        var y1 = Math.max(a.y1, b.y1);
        var x2 = Math.min(a.x2, b.x2);
        var y2 = Math.min(a.y2, b.y2);
        
        return jQuery(
            {
                left:   x1,
                top:    y1,
                width:  (x2 - x1),
                height: (y2 - y1)
            }
        ).coords().get();
    };
    
    fn.calculate_overlapped_area = function (coords) {
        return (coords.width * coords.height);
    };
    
    fn.manage_colliders_start_stop = function (new_colliders_coords, start_callback, stop_callback) {
        var last = this.last_colliders_coords;
        
        for (var i = 0, il = last.length; i < il; i++) {
            if (jQuery.inArray(last[i], new_colliders_coords) === -1) {
                start_callback.call(this, last[i]);
            }
        }
        
        for (var j = 0, jl = new_colliders_coords.length; j < jl; j++) {
            if (jQuery.inArray(new_colliders_coords[j], last) === -1) {
                stop_callback.call(this, new_colliders_coords[j]);
            }
            
        }
    };
    
    fn.find_collisions = function (player_data_coords) {
        var self = this;
        var overlapping_region = this.options.overlapping_region;
        var colliders_coords = [];
        var colliders_data = [];
        var jQuerycolliders = (this.colliders || this.jQuerycolliders);
        var count = jQuerycolliders.length;
        var player_coords = self.jQueryelement.coords()
            .update(player_data_coords || false).get();
        
        while (count--) {
            var jQuerycollider = self.jQuerycolliders ?
                jQuery(jQuerycolliders[count]) : jQuerycolliders[count];
            var jQuerycollider_coords_ins = (jQuerycollider.isCoords) ?
                jQuerycollider : jQuerycollider.coords();
            var collider_coords = jQuerycollider_coords_ins.get();
            var overlaps = self.overlaps(player_coords, collider_coords);
            
            if (!overlaps) {
                continue;
            }
            
            var region = self.detect_overlapping_region(
                player_coords,
                collider_coords
            );
            
            //todo: make this an option
            if (region === overlapping_region || overlapping_region === 'all') {
                
                var area_coords = self.calculate_overlapped_area_coords(
                    player_coords,
                    collider_coords
                );
                var area = self.calculate_overlapped_area(area_coords);
                var collider_data = {
                    area:          area,
                    area_coords:   area_coords,
                    region:        region,
                    coords:        collider_coords,
                    player_coords: player_coords,
                    el:            jQuerycollider
                };
                
                if (self.options.on_overlap) {
                    self.options.on_overlap.call(this, collider_data);
                }
                colliders_coords.push(jQuerycollider_coords_ins);
                colliders_data.push(collider_data);
            }
        }
        
        if (self.options.on_overlap_stop || self.options.on_overlap_start) {
            this.manage_colliders_start_stop(
                colliders_coords,
                self.options.on_overlap_start, self.options.on_overlap_stop
            );
        }
        
        this.last_colliders_coords = colliders_coords;
        
        return colliders_data;
    };
    
    fn.get_closest_colliders = function (player_data_coords) {
        var colliders = this.find_collisions(player_data_coords);
        
        colliders.sort(
            function (a, b) {
                /* if colliders are being overlapped by the "C" (center) region,
                 * we have to set a lower index in the array to which they are placed
                 * above in the grid. */
                if (a.region === 'C' && b.region === 'C') {
                    if (a.coords.y1 < b.coords.y1 || a.coords.x1 < b.coords.x1) {
                        return -1;
                    } else {
                        return 1;
                    }
                }
                
                if (a.area < b.area) {
                    return 1;
                }
                
                return 1;
            }
        );
        return colliders;
    };
    
    fn.set_colliders = function (colliders) {
        if (typeof colliders === 'string' || colliders instanceof jQuery) {
            this.jQuerycolliders = jQuery(
                colliders,
                this.options.colliders_context
            ).not(this.jQueryelement);
        } else {
            this.colliders = jQuery(colliders);
        }
    };
    
    //jQuery adapter
    jQuery.fn.collision = function (collider, options) {
        return new Collision(this, collider, options);
    };
    
    return Collision;
    
}));

;
(function (window, undefined) {
    
    /* Delay, debounce and throttle functions taken from underscore.js
     *
     * Copyright (c) 2009-2013 Jeremy Ashkenas, DocumentCloud and
     * Investigative Reporters & Editors
     *
     * Permission is hereby granted, free of charge, to any person
     * obtaining a copy of this software and associated documentation
     * files (the "Software"), to deal in the Software without
     * restriction, including without limitation the rights to use,
     * copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the
     * Software is furnished to do so, subject to the following
     * conditions:
     *
     * The above copyright notice and this permission notice shall be
     * included in all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
     * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
     * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
     * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
     * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
     * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
     * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
     * OTHER DEALINGS IN THE SOFTWARE.
     */
    
    window.delay = function (func, wait) {
        var args = Array.prototype.slice.call(arguments, 2);
        return setTimeout(
            function () {
                return func.apply(null, args);
            }, wait
        );
    };
    
    window.debounce = function (func, wait, immediate) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            var later = function () {
                timeout = null;
                if (!immediate) {
                    func.apply(context, args);
                }
            };
            if (immediate && !timeout) {
                func.apply(context, args);
            }
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };
    
    window.throttle = function (func, wait) {
        var context, args, timeout, throttling, more, result;
        var whenDone = debounce(
            function () {
                more = throttling = false;
            }, wait
        );
        return function () {
            context = this;
            args = arguments;
            var later = function () {
                timeout = null;
                if (more) {
                    func.apply(context, args);
                }
                whenDone();
            };
            if (!timeout) {
                timeout = setTimeout(later, wait);
            }
            if (throttling) {
                more = true;
            } else {
                result = func.apply(context, args);
            }
            whenDone();
            throttling = true;
            return result;
        };
    };
    
})(window);

;
(function (root, factory) {
    
    if (typeof define === 'function' && define.amd) {
        define('gridster-draggable', ['jquery'], factory);
    } else {
        root.GridsterDraggable = factory(root.jQuery || root.jQuery);
    }
    
}(this, function (jQuery) {
    
    var defaults = {
        items:           'li',
        distance:        1,
        limit:           true,
        offset_left:     0,
        autoscroll:      true,
        ignore_dragging: ['INPUT', 'TEXTAREA', 'SELECT', 'BUTTON', ":not(.gs-drag-handle,.gs-resize-handle)"], // or function
        handle:          null,
        container_width: 0,  // 0 == auto
        move_element:    true,
        helper:          false,  // or 'clone'
        remove_helper:   true
        // drag: function(e) {},
        // start : function(e, ui) {},
        // stop : function(e) {}
    };
    
    var jQuerywindow = jQuery(window);
    var dir_map = {x: 'left', y: 'top'};
    var isTouch = !!('ontouchstart' in window);
    
    var capitalize = function (str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    };
    
    var idCounter = 0;
    var uniqId = function () {
        return ++idCounter + '';
    }
    
    /**
     * Basic drag implementation for DOM elements inside a container.
     * Provide start/stop/drag callbacks.
     *
     * @class       Draggable
     * @param       {HTMLElement} el The HTMLelement that contains all the widgets
     *  to be dragged.
     * @param       {Object} [options] An Object with all options you want to
     *        overwrite:
     * @param       {HTMLElement|String} [options.items] Define who will
     *     be the draggable items. Can be a CSS Selector String or a
     *     collection of HTMLElements.
     * @param       {Number} [options.distance] Distance in pixels after mousedown
     *     the mouse must move before dragging should start.
     * @param       {Boolean} [options.limit] Constrains dragging to the width of
     *     the container
     * @param       {Object|Function} [options.ignore_dragging] Array of node names
     *      that sould not trigger dragging, by default is `['INPUT', 'TEXTAREA',
     *      'SELECT', 'BUTTON']`. If a function is used return true to ignore dragging.
     * @param       {offset_left} [options.offset_left] Offset added to the item
     *     that is being dragged.
     * @param       {Number} [options.drag] Executes a callback when the mouse is
     *     moved during the dragging.
     * @param       {Number} [options.start] Executes a callback when the drag
     *     starts.
     * @param       {Number} [options.stop] Executes a callback when the drag stops.
     * @return      {Object} Returns `el`.
     * @constructor
     */
    function Draggable(el, options) {
        this.options = jQuery.extend({}, defaults, options);
        this.jQuerydocument = jQuery(document);
        this.jQuerycontainer = jQuery(el);
        this.jQuerydragitems = jQuery(this.options.items, this.jQuerycontainer);
        this.is_dragging = false;
        this.player_min_left = 0 + this.options.offset_left;
        this.id = uniqId();
        this.ns = '.gridster-draggable-' + this.id;
        this.init();
    }
    
    Draggable.defaults = defaults;
    
    var fn = Draggable.prototype;
    
    fn.init = function () {
        var pos = this.jQuerycontainer.css('position');
        this.calculate_dimensions();
        this.jQuerycontainer.css('position', pos === 'static' ? 'relative' : pos);
        this.disabled = false;
        this.events();
        
        jQuery(window).bind(
            this.nsEvent('resize'),
            throttle(jQuery.proxy(this.calculate_dimensions, this), 200)
        );
    };
    
    fn.nsEvent = function (ev) {
        return (ev || '') + this.ns;
    };
    
    fn.events = function () {
        this.pointer_events = {
            start: this.nsEvent('touchstart') + ' ' + this.nsEvent('mousedown'),
            move:  this.nsEvent('touchmove') + ' ' + this.nsEvent('mousemove'),
            end:   this.nsEvent('touchend') + ' ' + this.nsEvent('mouseup')
        };
        
        this.jQuerycontainer.on(
            this.nsEvent('selectstart'),
            jQuery.proxy(this.on_select_start, this)
        );
        
        this.jQuerycontainer.on(
            this.pointer_events.start, this.options.items,
            jQuery.proxy(this.drag_handler, this)
        );
        
        this.jQuerydocument.on(
            this.pointer_events.end, jQuery.proxy(
                function (e) {
                    this.is_dragging = false;
                    if (this.disabled) {
                        return;
                    }
                    this.jQuerydocument.off(this.pointer_events.move);
                    if (this.drag_start) {
                        this.on_dragstop(e);
                    }
                }, this
            )
        );
    };
    
    fn.get_actual_pos = function (jQueryel) {
        var pos = jQueryel.position();
        return pos;
    };
    
    fn.get_mouse_pos = function (e) {
        if (e.originalEvent && e.originalEvent.touches) {
            var oe = e.originalEvent;
            e = oe.touches.length ? oe.touches[0] : oe.changedTouches[0];
        }
        
        return {
            left: e.clientX,
            top:  e.clientY
        };
    };
    
    fn.get_offset = function (e) {
        e.preventDefault();
        var mouse_actual_pos = this.get_mouse_pos(e);
        var diff_x = Math.round(
            mouse_actual_pos.left - this.mouse_init_pos.left
        );
        var diff_y = Math.round(mouse_actual_pos.top - this.mouse_init_pos.top);
        
        var left = Math.round(
            this.el_init_offset.left +
            diff_x - this.baseX + jQuery(window).scrollLeft() - this.win_offset_x
        );
        var top = Math.round(
            this.el_init_offset.top +
            diff_y - this.baseY + jQuery(window).scrollTop() - this.win_offset_y
        );
        
        if (this.options.limit) {
            if (left > this.player_max_left) {
                left = this.player_max_left;
            } else if (left < this.player_min_left) {
                left = this.player_min_left;
            }
        }
        
        return {
            position: {
                left: left,
                top:  top
            },
            pointer:  {
                left:      mouse_actual_pos.left,
                top:       mouse_actual_pos.top,
                diff_left: diff_x + (jQuery(window).scrollLeft() - this.win_offset_x),
                diff_top:  diff_y + (jQuery(window).scrollTop() - this.win_offset_y)
            }
        };
    };
    
    fn.get_drag_data = function (e) {
        var offset = this.get_offset(e);
        offset.jQueryplayer = this.jQueryplayer;
        offset.jQueryhelper = this.helper ? this.jQueryhelper : this.jQueryplayer;
        
        return offset;
    };
    
    fn.set_limits = function (container_width) {
        container_width || (container_width = this.jQuerycontainer.width());
        this.player_max_left = (container_width - this.player_width + -this.options.offset_left);
        
        this.options.container_width = container_width;
        
        return this;
    };
    
    fn.scroll_in = function (axis, data) {
        var dir_prop = dir_map[axis];
        
        var area_size = 50;
        var scroll_inc = 30;
        
        var is_x = axis === 'x';
        var window_size = is_x ? this.window_width : this.window_height;
        var doc_size = is_x ? jQuery(document).width() : jQuery(document).height();
        var player_size = is_x ? this.jQueryplayer.width() : this.jQueryplayer.height();
        
        var next_scroll;
        var scroll_offset = jQuerywindow['scroll' + capitalize(dir_prop)]();
        var min_window_pos = scroll_offset;
        var max_window_pos = min_window_pos + window_size;
        
        var mouse_next_zone = max_window_pos - area_size;  // down/right
        var mouse_prev_zone = min_window_pos + area_size;  // up/left
        
        var abs_mouse_pos = min_window_pos + data.pointer[dir_prop];
        
        var max_player_pos = (doc_size - window_size + player_size);
        
        if (abs_mouse_pos >= mouse_next_zone) {
            next_scroll = scroll_offset + scroll_inc;
            if (next_scroll < max_player_pos) {
                jQuerywindow['scroll' + capitalize(dir_prop)](next_scroll);
                this['scroll_offset_' + axis] += scroll_inc;
            }
        }
        
        if (abs_mouse_pos <= mouse_prev_zone) {
            next_scroll = scroll_offset - scroll_inc;
            if (next_scroll > 0) {
                jQuerywindow['scroll' + capitalize(dir_prop)](next_scroll);
                this['scroll_offset_' + axis] -= scroll_inc;
            }
        }
        
        return this;
    };
    
    fn.manage_scroll = function (data) {
        this.scroll_in('x', data);
        this.scroll_in('y', data);
    };
    
    fn.calculate_dimensions = function (e) {
        this.window_height = jQuerywindow.height();
        this.window_width = jQuerywindow.width();
    };
    
    fn.drag_handler = function (e) {
        var node = e.target.nodeName;
        // skip if drag is disabled, or click was not done with the mouse primary button
        if (this.disabled || e.which !== 1 && !isTouch) {
            return;
        }
        
        if (this.ignore_drag(e)) {
            return;
        }
        
        var self = this;
        var first = true;
        this.jQueryplayer = jQuery(e.currentTarget);
        
        this.el_init_pos = this.get_actual_pos(this.jQueryplayer);
        this.mouse_init_pos = this.get_mouse_pos(e);
        this.offsetY = this.mouse_init_pos.top - this.el_init_pos.top;
        
        this.jQuerydocument.on(
            this.pointer_events.move, function (mme) {
                var mouse_actual_pos = self.get_mouse_pos(mme);
                var diff_x = Math.abs(
                    mouse_actual_pos.left - self.mouse_init_pos.left
                );
                var diff_y = Math.abs(
                    mouse_actual_pos.top - self.mouse_init_pos.top
                );
                if (!(diff_x > self.options.distance
                    || diff_y > self.options.distance)
                ) {
                    return false;
                }
                
                if (first) {
                    first = false;
                    self.on_dragstart.call(self, mme);
                    return false;
                }
                
                if (self.is_dragging === true) {
                    self.on_dragmove.call(self, mme);
                }
                
                return false;
            }
        );
        
        if (!isTouch) {
            return false;
        }
    };
    
    fn.on_dragstart = function (e) {
        e.preventDefault();
        
        if (this.is_dragging) {
            return this;
        }
        
        this.drag_start = this.is_dragging = true;
        var offset = this.jQuerycontainer.offset();
        this.baseX = Math.round(offset.left);
        this.baseY = Math.round(offset.top);
        this.initial_container_width = this.options.container_width || this.jQuerycontainer.width();
        
        if (this.options.helper === 'clone') {
            this.jQueryhelper = this.jQueryplayer.clone()
                .appendTo(this.jQuerycontainer).addClass('helper');
            this.helper = true;
        } else {
            this.helper = false;
        }
        
        this.win_offset_y = jQuery(window).scrollTop();
        this.win_offset_x = jQuery(window).scrollLeft();
        this.scroll_offset_y = 0;
        this.scroll_offset_x = 0;
        this.el_init_offset = this.jQueryplayer.offset();
        this.player_width = this.jQueryplayer.width();
        this.player_height = this.jQueryplayer.height();
        
        this.set_limits(this.options.container_width);
        
        if (this.options.start) {
            this.options.start.call(this.jQueryplayer, e, this.get_drag_data(e));
        }
        return false;
    };
    
    fn.on_dragmove = function (e) {
        var data = this.get_drag_data(e);
        
        this.options.autoscroll && this.manage_scroll(data);
        
        if (this.options.move_element) {
            (this.helper ? this.jQueryhelper : this.jQueryplayer).css(
                {
                    'position': 'absolute',
                    'left':     data.position.left,
                    'top':      data.position.top
                }
            );
        }
        
        var last_position = this.last_position || data.position;
        data.prev_position = last_position;
        
        if (this.options.drag) {
            this.options.drag.call(this.jQueryplayer, e, data);
        }
        
        this.last_position = data.position;
        return false;
    };
    
    fn.on_dragstop = function (e) {
        var data = this.get_drag_data(e);
        this.drag_start = false;
        
        if (this.options.stop) {
            this.options.stop.call(this.jQueryplayer, e, data);
        }
        
        if (this.helper && this.options.remove_helper) {
            this.jQueryhelper.remove();
        }
        
        return false;
    };
    
    fn.on_select_start = function (e) {
        if (this.disabled) {
            return;
        }
        
        if (this.ignore_drag(e)) {
            return;
        }
        
        return false;
    };
    
    fn.enable = function () {
        this.disabled = false;
    };
    
    fn.disable = function () {
        this.disabled = true;
    };
    
    fn.destroy = function () {
        this.disable();
        
        this.jQuerycontainer.off(this.ns);
        this.jQuerydocument.off(this.ns);
        jQuery(window).off(this.ns);
        
        jQuery.removeData(this.jQuerycontainer, 'drag');
    };
    
    fn.ignore_drag = function (event) {
        if (this.options.handle) {
            return !jQuery(event.target).is(this.options.handle);
        }
        
        if (jQuery.isFunction(this.options.ignore_dragging)) {
            return this.options.ignore_dragging(event);
        }
        
        return jQuery(event.target).is(this.options.ignore_dragging.join(', '));
    };
    
    //jQuery adapter
    jQuery.fn.drag = function (options) {
        return new Draggable(this, options);
    };
    
    return Draggable;
    
}));

;
(function (root, factory) {
    
    if (typeof define === 'function' && define.amd) {
        define(['jquery', 'gridster-draggable', 'gridster-collision'], factory);
    } else {
        root.Gridster = factory(
            root.jQuery || root.jQuery, root.GridsterDraggable,
            root.GridsterCollision
        );
    }
    
}(this, function (jQuery, Draggable, Collision) {
    
    var defaults = {
        namespace:                '',
        widget_selector:          'li',
        widgetMargins:            [10, 10],
        widgetBaseDimensions:     [400, 225],
        extra_rows:               0,
        extra_cols:               0,
        min_cols:                 30,
        max_cols:                 Infinity,
        min_rows:                 30,
        max_sizeX:                false,
        autogrow_cols:            false,
        autogenerate_stylesheet:  true,
        avoid_overlapped_widgets: true,
        auto_init:                true,
        serializeParams:          function (jQueryw, wgd) {
            return {
                col:   wgd.col,
                row:   wgd.row,
                sizeX: wgd.sizeX,
                sizeY: wgd.sizeY
            };
        },
        collision:                {},
        draggable:                {
            items:           '.gs-w',
            distance:        4,
            ignore_dragging: Draggable.defaults.ignore_dragging.slice(0)
        },
        resize:                   {
            enabled:          false,
            axes:             ['both'],
            handle_append_to: '',
            handle_class:     'gs-resize-handle',
            max_size:         [Infinity, Infinity],
            min_size:         [1, 1]
        }
    };
    
    /**
     * @class Gridster
     * @uses Draggable
     * @uses Collision
     * @param {HTMLElement} el The HTMLelement that contains all the widgets.
     * @param {Object} [options] An Object with all options you want to
     *        overwrite:
     *    @param {HTMLElement|String} [options.widget_selector] Define who will
     *     be the draggable widgets. Can be a CSS Selector String or a
     *     collection of HTMLElements
     *    @param {Array} [options.widgetMargins] Margin between widgets.
     *     The first index for the horizontal margin (left, right) and
     *     the second for the vertical margin (top, bottom).
     *    @param {Array} [options.widgetBaseDimensions] Base widget dimensions
     *     in pixels. The first index for the width and the second for the
     *     height.
     *    @param {Number} [options.extra_cols] Add more columns in addition to
     *     those that have been calculated.
     *    @param {Number} [options.extra_rows] Add more rows in addition to
     *     those that have been calculated.
     *    @param {Number} [options.min_cols] The minimum required columns.
     *    @param {Number} [options.max_cols] The maximum columns possible (set to null
     *     for no maximum).
     *    @param {Number} [options.min_rows] The minimum required rows.
     *    @param {Number} [options.max_sizeX] The maximum number of columns
     *     that a widget can span.
     *    @param {Boolean} [options.autogenerate_stylesheet] If true, all the
     *     CSS required to position all widgets in their respective columns
     *     and rows will be generated automatically and injected to the
     *     `<head>` of the document. You can set this to false, and write
     *     your own CSS targeting rows and cols via data-attributes like so:
     *     `[data-col="1"] { left: 10px; }`
     *    @param {Boolean} [options.avoid_overlapped_widgets] Avoid that widgets loaded
     *     from the DOM can be overlapped. It is helpful if the positions were
     *     bad stored in the database or if there was any conflict.
     *    @param {Boolean} [options.auto_init] Automatically call gridster init
     *     method or not when the plugin is instantiated.
     *    @param {Function} [options.serialize_params] Return the data you want
     *     for each widget in the serialization. Two arguments are passed:
     *     `jQueryw`: the jQuery wrapped HTMLElement, and `wgd`: the grid
     *     coords object (`col`, `row`, `sizeX`, `sizeY`).
     *    @param {Object} [options.collision] An Object with all options for
     *     Collision class you want to overwrite. See Collision docs for
     *     more info.
     *    @param {Object} [options.draggable] An Object with all options for
     *     Draggable class you want to overwrite. See Draggable docs for more
     *     info.
     *       @param {Object|Function} [options.draggable.ignore_dragging] Note that
     *        if you use a Function, and resize is enabled, you should ignore the
     *        resize handlers manually (options.resize.handle_class).
     *    @param {Object} [options.resize] An Object with resize config options.
     *       @param {Boolean} [options.resize.enabled] Set to true to enable
     *        resizing.
     *       @param {Array} [options.resize.axes] Axes in which widgets can be
     *        resized. Possible values: ['x', 'y', 'both'].
     *       @param {String} [options.resize.handle_append_to] Set a valid CSS
     *        selector to append resize handles to.
     *       @param {String} [options.resize.handle_class] CSS class name used
     *        by resize handles.
     *       @param {Array} [options.resize.max_size] Limit widget dimensions
     *        when resizing. Array values should be integers:
     *        `[max_cols_occupied, max_rows_occupied]`
     *       @param {Array} [options.resize.min_size] Limit widget dimensions
     *        when resizing. Array values should be integers:
     *        `[min_cols_occupied, min_rows_occupied]`
     *       @param {Function} [options.resize.start] Function executed
     *        when resizing starts.
     *       @param {Function} [otions.resize.resize] Function executed
     *        during the resizing.
     *       @param {Function} [options.resize.stop] Function executed
     *        when resizing stops.
     *
     * @constructor
     */
    function Gridster(el, options) {
        this.options = jQuery.extend(true, {}, defaults, options);
        this.jQueryel = jQuery(el);
        this.jQuerywrapper = this.jQueryel.parent();
        this.jQuerywidgets = this.jQueryel.children(
            this.options.widget_selector
        ).addClass('gs-w');
        this.widgets = [];
        this.jQuerychanged = jQuery([]);
        this.wrapper_width = this.jQuerywrapper.width();
        this.min_widget_width = (this.options.widgetMargins[0] * 2) +
            this.options.widgetBaseDimensions[0];
        this.min_widget_height = (this.options.widgetMargins[1] * 2) +
            this.options.widgetBaseDimensions[1];
        
        this.generated_stylesheets = [];
        this.jQuerystyle_tags = jQuery([]);
        
        this.options.auto_init && this.init();
    }
    
    Gridster.defaults = defaults;
    Gridster.generated_stylesheets = [];
    
    /**
     * Sorts an Array of grid coords objects (representing the grid coords of
     * each widget) in ascending way.
     *
     * @method sort_by_row_asc
     * @param  {Array} widgets Array of grid coords objects
     * @return {Array} Returns the array sorted.
     */
    Gridster.sort_by_row_asc = function (widgets) {
        widgets = widgets.sort(
            function (a, b) {
                if (!a.row) {
                    a = jQuery(a).coords().grid;
                    b = jQuery(b).coords().grid;
                }
                
                if (a.row > b.row) {
                    return 1;
                }
                return -1;
            }
        );
        
        return widgets;
    };
    
    /**
     * Sorts an Array of grid coords objects (representing the grid coords of
     * each widget) placing first the empty cells upper left.
     *
     * @method sort_by_row_and_col_asc
     * @param  {Array} widgets Array of grid coords objects
     * @return {Array} Returns the array sorted.
     */
    Gridster.sort_by_row_and_col_asc = function (widgets) {
        widgets = widgets.sort(
            function (a, b) {
                if (a.row > b.row || a.row === b.row && a.col > b.col) {
                    return 1;
                }
                return -1;
            }
        );
        
        return widgets;
    };
    
    /**
     * Sorts an Array of grid coords objects by column (representing the grid
     * coords of each widget) in ascending way.
     *
     * @method sort_by_col_asc
     * @param  {Array} widgets Array of grid coords objects
     * @return {Array} Returns the array sorted.
     */
    Gridster.sort_by_col_asc = function (widgets) {
        widgets = widgets.sort(
            function (a, b) {
                if (a.col > b.col) {
                    return 1;
                }
                return -1;
            }
        );
        
        return widgets;
    };
    
    /**
     * Sorts an Array of grid coords objects (representing the grid coords of
     * each widget) in descending way.
     *
     * @method sort_by_row_desc
     * @param  {Array} widgets Array of grid coords objects
     * @return {Array} Returns the array sorted.
     */
    Gridster.sort_by_row_desc = function (widgets) {
        widgets = widgets.sort(
            function (a, b) {
                if (a.row + a.sizeY < b.row + b.sizeY) {
                    return 1;
                }
                return -1;
            }
        );
        return widgets;
    };
    
    /**
     * Instance Methods
     **/
    
    var fn = Gridster.prototype;
    
    fn.init = function () {
        this.options.resize.enabled && this.setup_resize();
        this.generate_grid_and_stylesheet();
        this.get_widgets_from_DOM();
        this.setDomGridHeight();
        this.setDomGridWidth();
        this.jQuerywrapper.addClass('ready');
        this.draggable();
        this.options.resize.enabled && this.resizable();
        
        jQuery(window).bind(
            'resize.gridster', throttle(
                jQuery.proxy(this.recalculate_faux_grid, this), 200
            )
        );
    };
    
    /**
     * Disables dragging.
     *
     * @method disable
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.disable = function () {
        this.jQuerywrapper.find('.player-revert').removeClass('player-revert');
        this.drag_api.disable();
        return this;
    };
    
    /**
     * Enables dragging.
     *
     * @method enable
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.enable = function () {
        this.drag_api.enable();
        return this;
    };
    
    /**
     * Disables drag-and-drop widget resizing.
     *
     * @method disable
     * @return {Class} Returns instance of gridster Class.
     */
    fn.disable_resize = function () {
        this.jQueryel.addClass('gs-resize-disabled');
        this.resize_api.disable();
        return this;
    };
    
    /**
     * Enables drag-and-drop widget resizing.
     *
     * @method enable
     * @return {Class} Returns instance of gridster Class.
     */
    fn.enableResize = function () {
        this.jQueryel.removeClass('gs-resize-disabled');
        this.resize_api.enable();
        return this;
    };
    
    /**
     * Add a new widget to the grid.
     *
     * @method add_widget
     * @param  {String|HTMLElement} html The string representing the HTML of the widget
     *  or the HTMLElement.
     * @param  {Number} [sizeX] The nº of rows the widget occupies horizontally.
     * @param  {Number} [sizeY] The nº of columns the widget occupies vertically.
     * @param  {Number} [col] The column the widget should start in.
     * @param  {Number} [row] The row the widget should start in.
     * @param  {Array} [max_size] max_size Maximun size (in units) for width and height.
     * @param  {Array} [min_size] min_size Minimum size (in units) for width and height.
     * @return {HTMLElement} Returns the jQuery wrapped HTMLElement representing.
     *  the widget that was just created.
     */
    fn.add_widget = function (html, sizeX, sizeY, col, row, max_size, min_size) {
        var pos;
        sizeX || (sizeX = 1);
        sizeY || (sizeY = 1);
        
        if (!col & !row) {
            pos = this.next_position(sizeX, sizeY);
        } else {
            pos = {
                col:   col,
                row:   row,
                sizeX: sizeX,
                sizeY: sizeY
            };
            
            this.empty_cells(col, row, sizeX, sizeY);
        }
        
        var jQueryw = jQuery(html).attr({
            'data-col':   pos.col,
            'data-row':   pos.row,
            'data-sizex': sizeX,
            'data-sizey': sizeY
        }).addClass('gs-w').appendTo(this.jQueryel).hide();
        
        this.jQuerywidgets = this.jQuerywidgets.add(jQueryw);
        
        this.registerWidget(jQueryw);
        
        this.add_faux_rows(pos.sizeY);
        //this.add_faux_cols(pos.sizeX);
        
        if (max_size) {
            this.set_widget_max_size(jQueryw, max_size);
        }
        
        if (min_size) {
            this.set_widget_min_size(jQueryw, min_size);
        }
        
        this.setDomGridWidth();
        this.setDomGridHeight();
        
        this.drag_api.set_limits(this.cols * this.min_widget_width);
        
        return jQueryw.fadeIn();
    };
    
    /**
     * Change widget size limits.
     *
     * @method set_widget_min_size
     * @param  {HTMLElement|Number} jQuerywidget The jQuery wrapped HTMLElement
     *  representing the widget or an index representing the desired widget.
     * @param  {Array} min_size Minimum size (in units) for width and height.
     * @return {HTMLElement} Returns instance of gridster Class.
     */
    fn.set_widget_min_size = function (jQuerywidget, min_size) {
        jQuerywidget = typeof jQuerywidget === 'number' ?
            this.jQuerywidgets.eq(jQuerywidget) : jQuerywidget;
        
        if (!jQuerywidget.length) {
            return this;
        }
        
        var wgd = jQuerywidget.data('coords').grid;
        wgd.min_sizeX = min_size[0];
        wgd.min_sizeY = min_size[1];
        
        return this;
    };
    
    /**
     * Change widget size limits.
     *
     * @method set_widget_max_size
     * @param  {HTMLElement|Number} jQuerywidget The jQuery wrapped HTMLElement
     *  representing the widget or an index representing the desired widget.
     * @param  {Array} max_size Maximun size (in units) for width and height.
     * @return {HTMLElement} Returns instance of gridster Class.
     */
    fn.set_widget_max_size = function (jQuerywidget, max_size) {
        jQuerywidget = typeof jQuerywidget === 'number' ?
            this.jQuerywidgets.eq(jQuerywidget) : jQuerywidget;
        
        if (!jQuerywidget.length) {
            return this;
        }
        
        var wgd = jQuerywidget.data('coords').grid;
        wgd.max_sizeX = max_size[0];
        wgd.max_sizeY = max_size[1];
        
        return this;
    };
    
    /**
     * Append the resize handle into a widget.
     *
     * @method add_resize_handle
     * @param  {HTMLElement} jQuerywidget The jQuery wrapped HTMLElement
     *  representing the widget.
     * @return {HTMLElement} Returns instance of gridster Class.
     */
    fn.add_resize_handle = function (jQueryw) {
        var append_to = this.options.resize.handle_append_to;
        jQuery(this.resize_handle_tpl).appendTo(append_to ? jQuery(append_to, jQueryw) : jQueryw);
        
        return this;
    };
    
    /**
     * Change the size of a widget. Width is limited to the current grid width.
     *
     * @method resize_widget
     * @param  {HTMLElement} jQuerywidget The jQuery wrapped HTMLElement
     *  representing the widget.
     * @param  {Number} sizeX The number of columns that will occupy the widget.
     *  By default <code>sizeX</code> is limited to the space available from
     *  the column where the widget begins, until the last column to the right.
     * @param  {Number} sizeY The number of rows that will occupy the widget.
     * @param  {Function} [callback] Function executed when the widget is removed.
     * @return {HTMLElement} Returns jQuerywidget.
     */
    fn.resize_widget = function (jQuerywidget, sizeX, sizeY, callback) {
        var wgd = jQuerywidget.coords().grid;
        var col = wgd.col;
        var max_cols = this.options.max_cols;
        var old_sizeY = wgd.sizeY;
        var old_col = wgd.col;
        var new_col = old_col;
        
        sizeX || (sizeX = wgd.sizeX);
        sizeY || (sizeY = wgd.sizeY);
        
        if (max_cols !== Infinity) {
            sizeX = Math.min(sizeX, max_cols - col + 1);
        }
        
        if (sizeY > old_sizeY) {
            this.add_faux_rows(Math.max(sizeY - old_sizeY, 0));
        }
        
        var player_rcol = (col + sizeX - 1);
        if (player_rcol > this.cols) {
            this.add_faux_cols(player_rcol - this.cols);
        }
        
        var new_grid_data = {
            col:   new_col,
            row:   wgd.row,
            sizeX: sizeX,
            sizeY: sizeY
        };
        
        this.mutate_widget_in_gridmap(jQuerywidget, wgd, new_grid_data);
        
        this.setDomGridHeight();
        this.setDomGridWidth();
        
        if (callback) {
            callback.call(this, new_grid_data.sizeX, new_grid_data.sizeY);
        }
        
        return jQuerywidget;
    };
    
    /**
     * Mutate widget dimensions and position in the grid map.
     *
     * @method mutate_widget_in_gridmap
     * @param  {HTMLElement} jQuerywidget The jQuery wrapped HTMLElement
     *  representing the widget to mutate.
     * @param  {Object} wgd Current widget grid data (col, row, sizeX, sizeY).
     * @param  {Object} new_wgd New widget grid data.
     * @return {HTMLElement} Returns instance of gridster Class.
     */
    fn.mutate_widget_in_gridmap = function (jQuerywidget, wgd, new_wgd) {
        var old_sizeX = wgd.sizeX;
        var old_sizeY = wgd.sizeY;
        
        var old_cells_occupied = this.get_cells_occupied(wgd);
        var new_cells_occupied = this.get_cells_occupied(new_wgd);
        
        var empty_cols = [];
        jQuery.each(
            old_cells_occupied.cols, function (i, col) {
                if (jQuery.inArray(col, new_cells_occupied.cols) === -1) {
                    empty_cols.push(col);
                }
            }
        );
        
        var occupied_cols = [];
        jQuery.each(
            new_cells_occupied.cols, function (i, col) {
                if (jQuery.inArray(col, old_cells_occupied.cols) === -1) {
                    occupied_cols.push(col);
                }
            }
        );
        
        var empty_rows = [];
        jQuery.each(
            old_cells_occupied.rows, function (i, row) {
                if (jQuery.inArray(row, new_cells_occupied.rows) === -1) {
                    empty_rows.push(row);
                }
            }
        );
        
        var occupied_rows = [];
        jQuery.each(
            new_cells_occupied.rows, function (i, row) {
                if (jQuery.inArray(row, old_cells_occupied.rows) === -1) {
                    occupied_rows.push(row);
                }
            }
        );
        
        this.remove_from_gridmap(wgd);
        
        if (occupied_cols.length) {
            var cols_to_empty = [
                new_wgd.col, new_wgd.row, new_wgd.sizeX, Math.min(old_sizeY, new_wgd.sizeY), jQuerywidget
            ];
            this.empty_cells.apply(this, cols_to_empty);
        }
        
        if (occupied_rows.length) {
            var rows_to_empty = [new_wgd.col, new_wgd.row, new_wgd.sizeX, new_wgd.sizeY, jQuerywidget];
            this.empty_cells.apply(this, rows_to_empty);
        }
        
        // not the same that wgd = new_wgd;
        wgd.col = new_wgd.col;
        wgd.row = new_wgd.row;
        wgd.sizeX = new_wgd.sizeX;
        wgd.sizeY = new_wgd.sizeY;
        
        this.add_to_gridmap(new_wgd, jQuerywidget);
        
        jQuerywidget.removeClass('player-revert');
        
        //update coords instance attributes
        jQuerywidget.data('coords').update({
            width:  (new_wgd.sizeX * this.options.widgetBaseDimensions[0] +
            ((new_wgd.sizeX - 1) * this.options.widgetMargins[0]) * 2),
            height: (new_wgd.sizeY * this.options.widgetBaseDimensions[1] +
            ((new_wgd.sizeY - 1) * this.options.widgetMargins[1]) * 2)
        });
        
        jQuerywidget.attr({
            'data-col':   new_wgd.col,
            'data-row':   new_wgd.row,
            'data-sizex': new_wgd.sizeX,
            'data-sizey': new_wgd.sizeY
        });
        
        if (empty_cols.length) {
            var cols_to_remove_holes = [
                empty_cols[0], new_wgd.row,
                empty_cols.length,
                Math.min(old_sizeY, new_wgd.sizeY),
                jQuerywidget
            ];
            
            this.remove_empty_cells.apply(this, cols_to_remove_holes);
        }
        
        if (empty_rows.length) {
            var rows_to_remove_holes = [
                new_wgd.col, new_wgd.row, new_wgd.sizeX, new_wgd.sizeY, jQuerywidget
            ];
            this.remove_empty_cells.apply(this, rows_to_remove_holes);
        }
        
        this.move_widget_up(jQuerywidget);
        
        return this;
    };
    
    /**
     * Move down widgets in cells represented by the arguments col, row, sizeX,
     * sizeY
     *
     * @method empty_cells
     * @param  {Number} col The column where the group of cells begin.
     * @param  {Number} row The row where the group of cells begin.
     * @param  {Number} sizeX The number of columns that the group of cells
     * occupy.
     * @param  {Number} sizeY The number of rows that the group of cells
     * occupy.
     * @param  {HTMLElement} jQueryexclude Exclude widgets from being moved.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.empty_cells = function (col, row, sizeX, sizeY, jQueryexclude) {
        var jQuerynexts = this.widgets_below(
            {
                col:   col,
                row:   row - sizeY,
                sizeX: sizeX,
                sizeY: sizeY
            }
        );
        
        jQuerynexts.not(jQueryexclude).each(
            jQuery.proxy(
                function (i, w) {
                    var wgd = jQuery(w).coords().grid;
                    if (!(wgd.row <= (row + sizeY - 1))) {
                        return;
                    }
                    var diff = (row + sizeY) - wgd.row;
                    this.move_widget_down(jQuery(w), diff);
                }, this
            )
        );
        
        this.setDomGridHeight();
        
        return this;
    };
    
    /**
     * Move up widgets below cells represented by the arguments col, row, sizeX,
     * sizeY.
     *
     * @method remove_empty_cells
     * @param  {Number} col The column where the group of cells begin.
     * @param  {Number} row The row where the group of cells begin.
     * @param  {Number} sizeX The number of columns that the group of cells
     * occupy.
     * @param  {Number} sizeY The number of rows that the group of cells
     * occupy.
     * @param  {HTMLElement} exclude Exclude widgets from being moved.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.remove_empty_cells = function (col, row, sizeX, sizeY, exclude) {
        var jQuerynexts = this.widgets_below(
            {
                col:   col,
                row:   row,
                sizeX: sizeX,
                sizeY: sizeY
            }
        );
        
        jQuerynexts.not(exclude).each(
            jQuery.proxy(
                function (i, widget) {
                    this.move_widget_up(jQuery(widget), sizeY);
                }, this
            )
        );
        
        this.setDomGridHeight();
        
        return this;
    };
    
    /**
     * Get the most left column below to add a new widget.
     *
     * @method next_position
     * @param  {Number} sizeX The nº of rows the widget occupies horizontally.
     * @param  {Number} sizeY The nº of columns the widget occupies vertically.
     * @return {Object} Returns a grid coords object representing the future
     *  widget coords.
     */
    fn.next_position = function (sizeX, sizeY) {
        sizeX || (sizeX = 1);
        sizeY || (sizeY = 1);
        var ga = this.gridmap;
        var cols_l = ga.length;
        var valid_pos = [];
        var rows_l;
        
        for (var c = 1; c < cols_l; c++) {
            rows_l = ga[c].length;
            for (var r = 1; r <= rows_l; r++) {
                var can_move_to = this.can_move_to(
                    {
                        sizeX: sizeX,
                        sizeY: sizeY
                    }, c, r
                );
                
                if (can_move_to) {
                    valid_pos.push(
                        {
                            col:   c,
                            row:   r,
                            sizeY: sizeY,
                            sizeX: sizeX
                        }
                    );
                }
            }
        }
        
        if (valid_pos.length) {
            return Gridster.sort_by_row_and_col_asc(valid_pos)[0];
        }
        return false;
    };
    
    /**
     * Remove a widget from the grid.
     *
     * @method removeWidget
     * @param  {HTMLElement} el The jQuery wrapped HTMLElement you want to remove.
     * @param  {Boolean|Function} silent If true, widgets below the removed one
     * will not move up. If a Function is passed it will be used as callback.
     * @param  {Function} callback Function executed when the widget is removed.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.removeWidget = function (el, silent, callback) {
        var jQueryel = el instanceof jQuery ? el : jQuery(el);
        var wgd = jQueryel.coords().grid;
        
        // if silent is a function assume it's a callback
        if (jQuery.isFunction(silent)) {
            callback = silent;
            silent = false;
        }
        
        this.cells_occupied_by_placeholder = {};
        this.jQuerywidgets = this.jQuerywidgets.not(jQueryel);
        
        var jQuerynexts = this.widgets_below(jQueryel);
        
        this.remove_from_gridmap(wgd);
        
        jQueryel.fadeOut(
            jQuery.proxy(
                function () {
                    jQueryel.remove();
                    
                    if (!silent) {
                        jQuerynexts.each(
                            jQuery.proxy(
                                function (i, widget) {
                                    this.move_widget_up(jQuery(widget), wgd.sizeY);
                                }, this
                            )
                        );
                    }
                    
                    this.setDomGridHeight();
                    
                    if (callback) {
                        callback.call(this, el);
                    }
                    this.options.remove();
                }, this
            )
        );
        
        return this;
    };
    
    /**
     * Remove all widgets from the grid.
     *
     * @method removeAllWidgets
     * @param  {Function} callback Function executed for each widget removed.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.removeAllWidgets = function (callback) {
        this.jQuerywidgets.each(
            jQuery.proxy(
                function (i, el) {
                    this.removeWidget(el, true, callback);
                }, this
            )
        );
        
        return this;
    };
    
    /**
     * Returns a serialized array of the widgets in the grid.
     *
     * @method serialize
     * @param  {HTMLElement} [jQuerywidgets] The collection of jQuery wrapped
     *  HTMLElements you want to serialize. If no argument is passed all widgets
     *  will be serialized.
     * @return {Array} Returns an Array of Objects with the data specified in
     *  the serialize_params option.
     */
    fn.serialize = function (jQuerywidgets) {
        jQuerywidgets || (jQuerywidgets = this.jQuerywidgets);
        
        var result = [];
        
        jQuerywidgets.map(
            jQuery.proxy(
                function (i, widget) {
                    var jQueryw = jQuery(widget);
                    var data = this.options.serializeParams(jQueryw, jQueryw.coords().grid);
                    
                    result.push(data);
                    
                }, this
            )
        );
        
        return result;
    };
    
    /**
     * Returns a serialized array of the widgets that have changed their
     *  position.
     *
     * @method serialize_changed
     * @return {Array} Returns an Array of Objects with the data specified in
     *  the serializeParams option.
     */
    fn.serialize_changed = function () {
        return this.serialize(this.jQuerychanged);
    };
    
    /**
     * Convert widgets from DOM elements to "widget grid data" Objects.
     *
     * @method dom_to_coords
     * @param  {HTMLElement} jQuerywidget The widget to be converted.
     */
    fn.dom_to_coords = function (jQuerywidget) {
        return {
            'col':       parseInt(jQuerywidget.attr('data-col'), 10),
            'row':       parseInt(jQuerywidget.attr('data-row'), 10),
            'sizeX':     parseInt(jQuerywidget.attr('data-sizex'), 10) || 1,
            'sizeY':     parseInt(jQuerywidget.attr('data-sizey'), 10) || 1,
            'max_sizeX': parseInt(jQuerywidget.attr('data-max-sizex'), 10) || false,
            'max_sizeY': parseInt(jQuerywidget.attr('data-max-sizey'), 10) || false,
            'min_sizeX': parseInt(jQuerywidget.attr('data-min-sizex'), 10) || false,
            'min_sizeY': parseInt(jQuerywidget.attr('data-min-sizey'), 10) || false,
            'el':        jQuerywidget
        };
    };
    
    /**
     * Creates the grid coords object representing the widget an add it to the
     * mapped array of positions.
     *
     * @method registerWidget
     * @param  {HTMLElement|Object} jQueryel jQuery wrapped HTMLElement representing
     *  the widget, or an "widget grid data" Object with (col, row, el ...).
     * @return {Boolean} Returns true if the widget final position is different
     *  than the original.
     */
    fn.registerWidget = function (jQueryel) {
        var isDOM = jQueryel instanceof jQuery;
        var wgd = isDOM ? this.dom_to_coords(jQueryel) : jQueryel;
        var posChanged = false;
        isDOM || (jQueryel = wgd.el);
        
        this.jQuerywidgets = this.jQuerywidgets.add(jQueryel);
        
        var isEmptyUpperRow = this.can_go_widget_up(wgd);
        if (isEmptyUpperRow) {
            wgd.row = isEmptyUpperRow;
            jQueryel.attr('data-row', isEmptyUpperRow);
            this.jQueryel.trigger('gridster:positionchanged', [wgd]);
            posChanged = true;
        }
        
        if (this.options.avoid_overlapped_widgets && !this.can_move_to(
                {sizeX: wgd.sizeX, sizeY: wgd.sizeY}, wgd.col, wgd.row
            )
        ) {
            jQuery.extend(wgd, this.next_position(wgd.sizeX, wgd.sizeY));
            jQueryel.attr({
                'data-col':   wgd.col,
                'data-row':   wgd.row,
                'data-sizex': wgd.sizeX,
                'data-sizey': wgd.sizeY
            });
            posChanged = true;
        }
        
        // attach Coord object to player data-coord attribute
        jQueryel.data('coords', jQueryel.coords());
        // Extend Coord object with grid position info
        jQueryel.data('coords').grid = wgd;
        
        this.add_to_gridmap(wgd, jQueryel);
        
        this.options.resize.enabled && this.add_resize_handle(jQueryel);
        
        return posChanged;
    };
    
    /**
     * Update in the mapped array of positions the value of cells represented by
     * the grid coords object passed in the `grid_data` param.
     *
     * @param  {Object} grid_data The grid coords object representing the cells
     *  to update in the mapped array.
     * @param  {HTMLElement|Boolean} value Pass `false` or the jQuery wrapped
     *  HTMLElement, depends if you want to delete an existing position or add
     *  a new one.
     * @method update_widget_position
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.update_widget_position = function (grid_data, value) {
        this.for_each_cell_occupied(
            grid_data, function (col, row) {
                if (!this.gridmap[col]) {
                    return this;
                }
                this.gridmap[col][row] = value;
            }
        );
        return this;
    };
    
    /**
     * Remove a widget from the mapped array of positions.
     *
     * @method remove_from_gridmap
     * @param  {Object} grid_data The grid coords object representing the cells
     *  to update in the mapped array.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.remove_from_gridmap = function (grid_data) {
        return this.update_widget_position(grid_data, false);
    };
    
    /**
     * Add a widget to the mapped array of positions.
     *
     * @method add_to_gridmap
     * @param  {Object} grid_data The grid coords object representing the cells
     *  to update in the mapped array.
     * @param  {HTMLElement|Boolean} value The value to set in the specified
     *  position .
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.add_to_gridmap = function (grid_data, value) {
        this.update_widget_position(grid_data, value || grid_data.el);
        
        if (grid_data.el) {
            var jQuerywidgets = this.widgets_below(grid_data.el);
            jQuerywidgets.each(
                jQuery.proxy(
                    function (i, widget) {
                        this.move_widget_up(jQuery(widget));
                    }, this
                )
            );
        }
    };
    
    /**
     * Make widgets draggable.
     *
     * @uses   Draggable
     * @method draggable
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.draggable = function () {
        var self = this;
        var draggable_options = jQuery.extend(
            true, {}, this.options.draggable, {
                offset_left:     this.options.widgetMargins[0],
                offset_top:      this.options.widgetMargins[1],
                container_width: this.cols * this.min_widget_width,
                limit:           true,
                start:           function (event, ui) {
                    self.jQuerywidgets.filter('.player-revert')
                        .removeClass('player-revert');
                    
                    self.jQueryplayer = jQuery(this);
                    self.jQueryhelper = jQuery(ui.jQueryhelper);
                    
                    self.helper = !self.jQueryhelper.is(self.jQueryplayer);
                    
                    self.on_start_drag.call(self, event, ui);
                    self.jQueryel.trigger('gridster:dragstart');
                },
                stop:            function (event, ui) {
                    self.on_stop_drag.call(self, event, ui);
                    self.jQueryel.trigger('gridster:dragstop');
                },
                drag:            throttle(
                    function (event, ui) {
                        self.on_drag.call(self, event, ui);
                        self.jQueryel.trigger('gridster:drag');
                    }, 60
                )
            }
        );
        
        this.drag_api = this.jQueryel.drag(draggable_options);
        return this;
    };
    
    /**
     * Bind resize events to get resize working.
     *
     * @method resizable
     * @return {Class} Returns instance of gridster Class.
     */
    fn.resizable = function () {
        this.resize_api = this.jQueryel.drag(
            {
                items:           '.' + this.options.resize.handle_class,
                offset_left:     this.options.widgetMargins[0],
                container_width: this.container_width,
                move_element:    false,
                resize:          true,
                limit:           this.options.autogrow_cols ? false : true,
                start:           jQuery.proxy(this.on_start_resize, this),
                stop:            jQuery.proxy(
                    function (event, ui) {
                        delay(
                            jQuery.proxy(
                                function () {
                                    this.on_stop_resize(event, ui);
                                }, this
                            ), 120
                        );
                    }, this
                ),
                drag:            throttle(jQuery.proxy(this.on_resize, this), 60)
            }
        );
        
        return this;
    };
    
    /**
     * Setup things required for resizing. Like build templates for drag handles.
     *
     * @method setup_resize
     * @return {Class} Returns instance of gridster Class.
     */
    fn.setup_resize = function () {
        this.resize_handle_class = this.options.resize.handle_class;
        var axes = this.options.resize.axes;
        var handle_tpl = '<span class="' + this.resize_handle_class + ' ' +
            this.resize_handle_class + '-{type}" />';
        
        this.resize_handle_tpl = jQuery.map(
            axes, function (type) {
                return handle_tpl.replace('{type}', type);
            }
        ).join('');
        
        return this;
    };
    
    /**
     * This function is executed when the player begins to be dragged.
     *
     * @method on_start_drag
     * @param  {Event} event The original browser event
     * @param  {Object} ui A prepared ui object with useful drag-related data
     */
    fn.on_start_drag = function (event, ui) {
        this.jQueryhelper.add(this.jQueryplayer).add(this.jQuerywrapper).addClass('dragging');
        
        this.highest_col = this.get_highest_occupied_cell().col;
        
        this.jQueryplayer.addClass('player');
        this.player_grid_data = this.jQueryplayer.coords().grid;
        this.placeholder_grid_data = jQuery.extend({}, this.player_grid_data);
        
        this.setDomGridHeight(
            this.jQueryel.height() +
            (this.player_grid_data.sizeY * this.min_widget_height)
        );
        
        this.setDomGridWidth(this.cols);
        
        var pgd_sizex = this.player_grid_data.sizeX;
        var cols_diff = this.cols - this.highest_col;
        
        if (this.options.autogrow_cols && cols_diff <= pgd_sizex) {
            this.add_faux_cols(Math.min(pgd_sizex - cols_diff, 1));
        }
        
        var colliders = this.faux_grid;
        var coords = this.jQueryplayer.data('coords').coords;
        
        this.cells_occupied_by_player = this.get_cells_occupied(
            this.player_grid_data
        );
        this.cells_occupied_by_placeholder = this.get_cells_occupied(
            this.placeholder_grid_data
        );
        
        this.last_cols = [];
        this.last_rows = [];
        
        // see jquery.collision.js
        this.collision_api = this.jQueryhelper.collision(
            colliders, this.options.collision
        );
        
        this.jQuerypreview_holder = jQuery(
            '<' + this.jQueryplayer.get(0).tagName + ' />', {
                'class':    'preview-holder',
                'data-row': this.jQueryplayer.attr('data-row'),
                'data-col': this.jQueryplayer.attr('data-col'),
                css:        {
                    width:  coords.width,
                    height: coords.height
                }
            }
        ).appendTo(this.jQueryel);
        
        if (this.options.draggable.start) {
            this.options.draggable.start.call(this, event, ui);
        }
    };
    
    /**
     * This function is executed when the player is being dragged.
     *
     * @method on_drag
     * @param  {Event} event The original browser event
     * @param  {Object} ui A prepared ui object with useful drag-related data
     */
    fn.on_drag = function (event, ui) {
        //break if dragstop has been fired
        if (this.jQueryplayer === null) {
            return false;
        }
        
        var abs_offset = {
            left: ui.position.left + this.baseX,
            top:  ui.position.top + this.baseY
        };
        
        // auto grow cols
        if (this.options.autogrow_cols) {
            var prcol = this.placeholder_grid_data.col +
                this.placeholder_grid_data.sizeX - 1;
            
            // "- 1" due to adding at least 1 column in on_start_drag
            if (prcol >= this.cols - 1 && this.options.max_cols >= this.cols + 1) {
                this.add_faux_cols(1);
                this.setDomGridWidth(this.cols + 1);
                this.drag_api.set_limits(this.container_width);
            }
            
            this.collision_api.set_colliders(this.faux_grid);
        }
        
        this.colliders_data = this.collision_api.get_closest_colliders(
            abs_offset
        );
        
        this.on_overlapped_column_change(
            this.on_start_overlapping_column, this.on_stop_overlapping_column
        );
        
        this.on_overlapped_row_change(
            this.on_start_overlapping_row, this.on_stop_overlapping_row
        );
        
        if (this.helper && this.jQueryplayer) {
            this.jQueryplayer.css(
                {
                    'left': ui.position.left,
                    'top':  ui.position.top
                }
            );
        }
        
        if (this.options.draggable.drag) {
            this.options.draggable.drag.call(this, event, ui);
        }
    };
    
    /**
     * This function is executed when the player stops being dragged.
     *
     * @method on_stop_drag
     * @param  {Event} event The original browser event
     * @param  {Object} ui A prepared ui object with useful drag-related data
     */
    fn.on_stop_drag = function (event, ui) {
        this.jQueryhelper.add(this.jQueryplayer).add(this.jQuerywrapper)
            .removeClass('dragging');
        
        ui.position.left = ui.position.left + this.baseX;
        ui.position.top = ui.position.top + this.baseY;
        this.colliders_data = this.collision_api.get_closest_colliders(
            ui.position
        );
        
        this.on_overlapped_column_change(
            this.on_start_overlapping_column,
            this.on_stop_overlapping_column
        );
        
        this.on_overlapped_row_change(
            this.on_start_overlapping_row,
            this.on_stop_overlapping_row
        );
        
        this.jQueryplayer.addClass('player-revert').removeClass('player')
            .attr(
                {
                    'data-col': this.placeholder_grid_data.col,
                    'data-row': this.placeholder_grid_data.row
                }
            ).css(
            {
                'left': '',
                'top':  ''
            }
        );
        
        this.jQuerychanged = this.jQuerychanged.add(this.jQueryplayer);
        
        this.cells_occupied_by_player = this.get_cells_occupied(
            this.placeholder_grid_data
        );
        this.set_cells_player_occupies(
            this.placeholder_grid_data.col, this.placeholder_grid_data.row
        );
        
        this.jQueryplayer.coords().grid.row = this.placeholder_grid_data.row;
        this.jQueryplayer.coords().grid.col = this.placeholder_grid_data.col;
        
        if (this.options.draggable.stop) {
            this.options.draggable.stop.call(this, event, ui);
        }
        
        this.jQuerypreview_holder.remove();
        
        this.jQueryplayer = null;
        this.jQueryhelper = null;
        this.placeholder_grid_data = {};
        this.player_grid_data = {};
        this.cells_occupied_by_placeholder = {};
        this.cells_occupied_by_player = {};
        
        this.setDomGridHeight();
        this.setDomGridWidth();
        
        if (this.options.autogrow_cols) {
            this.drag_api.set_limits(this.cols * this.min_widget_width);
        }
    };
    
    /**
     * This function is executed every time a widget starts to be resized.
     *
     * @method on_start_resize
     * @param  {Event} event The original browser event
     * @param  {Object} ui A prepared ui object with useful drag-related data
     */
    fn.on_start_resize = function (event, ui) {
        this.jQueryresized_widget = ui.jQueryplayer.closest('.gs-w');
        this.resize_coords = this.jQueryresized_widget.coords();
        this.resize_wgd = this.resize_coords.grid;
        this.resize_initial_width = this.resize_coords.coords.width;
        this.resize_initial_height = this.resize_coords.coords.height;
        this.resize_initial_sizex = this.resize_coords.grid.sizeX;
        this.resize_initial_sizey = this.resize_coords.grid.sizeY;
        this.resize_initial_col = this.resize_coords.grid.col;
        this.resize_last_sizex = this.resize_initial_sizex;
        this.resize_last_sizey = this.resize_initial_sizey;
        
        this.resize_max_sizeX = Math.min(
            this.resize_wgd.max_sizeX ||
            this.options.resize.max_size[0],
            this.options.max_cols - this.resize_initial_col + 1
        );
        this.resize_max_sizeY = this.resize_wgd.max_sizeY ||
            this.options.resize.max_size[1];
        
        this.resize_min_sizeX = (this.resize_wgd.min_sizeX ||
        this.options.resize.min_size[0] || 1);
        this.resize_min_sizeY = (this.resize_wgd.min_sizeY ||
        this.options.resize.min_size[1] || 1);
        
        this.resize_initial_last_col = this.get_highest_occupied_cell().col;
        
        this.setDomGridWidth(this.cols);
        
        this.resize_dir = {
            right:  ui.jQueryplayer.is('.' + this.resize_handle_class + '-x'),
            bottom: ui.jQueryplayer.is('.' + this.resize_handle_class + '-y')
        };
        
        this.jQueryresized_widget.css(
            {
                'min-width':  this.options.widgetBaseDimensions[0],
                'min-height': this.options.widgetBaseDimensions[1]
            }
        );
        
        var nodeName = this.jQueryresized_widget.get(0).tagName;
        this.jQueryresize_preview_holder = jQuery(
            '<' + nodeName + ' />', {
                'class':    'preview-holder resize-preview-holder',
                'data-row': this.jQueryresized_widget.attr('data-row'),
                'data-col': this.jQueryresized_widget.attr('data-col'),
                'css':      {
                    'width':  this.resize_initial_width,
                    'height': this.resize_initial_height
                }
            }
        ).appendTo(this.jQueryel);
        
        this.jQueryresized_widget.addClass('resizing');
        
        if (this.options.resize.start) {
            this.options.resize.start.call(this, event, ui, this.jQueryresized_widget);
        }
        
        this.jQueryel.trigger('gridster:resizestart');
    };
    
    /**
     * This function is executed every time a widget stops being resized.
     *
     * @method on_stop_resize
     * @param  {Event} event The original browser event
     * @param  {Object} ui A prepared ui object with useful drag-related data
     */
    fn.on_stop_resize = function (event, ui) {
        this.jQueryresized_widget
            .removeClass('resizing')
            .css(
                {
                    'width':  '',
                    'height': ''
                }
            );
        
        delay(
            jQuery.proxy(
                function () {
                    this.jQueryresize_preview_holder
                        .remove()
                        .css({
                            'min-width':  '',
                            'min-height': ''
                        });
                    if (this.options.resize.stop) {
                        this.options.resize.stop.call(this, event, ui, this.jQueryresized_widget);
                    }
                    
                    this.jQueryel.trigger('gridster:resizestop');
                }, this
            ), 300
        );
        
        this.setDomGridWidth();
        
        if (this.options.autogrow_cols) {
            this.drag_api.set_limits(this.cols * this.min_widget_width);
        }
    };
    
    /**
     * This function is executed when a widget is being resized.
     *
     * @method on_resize
     * @param  {Event} event The original browser event
     * @param  {Object} ui A prepared ui object with useful drag-related data
     */
    fn.on_resize = function (event, ui) {
        var rel_x = (ui.pointer.diff_left);
        var rel_y = (ui.pointer.diff_top);
        var wbd_x = this.options.widgetBaseDimensions[0];
        var wbd_y = this.options.widgetBaseDimensions[1];
        var margin_x = this.options.widgetMargins[0];
        var margin_y = this.options.widgetMargins[1];
        var max_sizeX = this.resize_max_sizeX;
        var min_sizeX = this.resize_min_sizeX;
        var max_sizeY = this.resize_max_sizeY;
        var min_sizeY = this.resize_min_sizeY;
        var autogrow = this.options.autogrow_cols;
        var width;
        var max_width = Infinity;
        var max_height = Infinity;
        
        var inc_units_x = Math.ceil((rel_x / (wbd_x + margin_x * 2)) - 0.2);
        var inc_units_y = Math.ceil((rel_y / (wbd_y + margin_y * 2)) - 0.2);
        
        var sizeX = Math.max(1, this.resize_initial_sizex + inc_units_x);
        var sizeY = Math.max(1, this.resize_initial_sizey + inc_units_y);
        
        var max_cols = (this.container_width / this.min_widget_width) -
            this.resize_initial_col + 1;
        var limit_width = ((max_cols * this.min_widget_width) - margin_x * 2);
        
        sizeX = Math.max(Math.min(sizeX, max_sizeX), min_sizeX);
        sizeX = Math.min(max_cols, sizeX);
        width = (max_sizeX * wbd_x) + ((sizeX - 1) * margin_x * 2);
        max_width = Math.min(width, limit_width);
        min_width = (min_sizeX * wbd_x) + ((sizeX - 1) * margin_x * 2);
        
        sizeY = Math.max(Math.min(sizeY, max_sizeY), min_sizeY);
        max_height = (max_sizeY * wbd_y) + ((sizeY - 1) * margin_y * 2);
        min_height = (min_sizeY * wbd_y) + ((sizeY - 1) * margin_y * 2);
        
        if (this.resize_dir.right) {
            sizeY = this.resize_initial_sizey;
        } else if (this.resize_dir.bottom) {
            sizeX = this.resize_initial_sizex;
        }
        
        if (autogrow) {
            var last_widget_col = this.resize_initial_col + sizeX - 1;
            if (autogrow && this.resize_initial_last_col <= last_widget_col) {
                this.setDomGridWidth(Math.max(last_widget_col + 1, this.cols));
                
                if (this.cols < last_widget_col) {
                    this.add_faux_cols(last_widget_col - this.cols);
                }
            }
        }
        
        var css_props = {};
        !this.resize_dir.bottom && (css_props.width = Math.max(
            Math.min(
                this.resize_initial_width + rel_x, max_width
            ), min_width
        ));
        !this.resize_dir.right && (css_props.height = Math.max(
            Math.min(
                this.resize_initial_height + rel_y, max_height
            ), min_height
        ));
        
        this.jQueryresized_widget.css(css_props);
        
        if (sizeX !== this.resize_last_sizex
            || sizeY !== this.resize_last_sizey
        ) {
            
            this.resize_widget(this.jQueryresized_widget, sizeX, sizeY);
            this.setDomGridWidth(this.cols);
            
            this.jQueryresize_preview_holder.css({
                'width':  '',
                'height': ''
            }).attr({
                'data-row':   this.jQueryresized_widget.attr('data-row'),
                'data-sizex': sizeX,
                'data-sizey': sizeY
            });
        }
        
        if (this.options.resize.resize) {
            this.options.resize.resize.call(this, event, ui, this.jQueryresized_widget);
        }
        
        this.jQueryel.trigger('gridster:resize');
        
        this.resize_last_sizex = sizeX;
        this.resize_last_sizey = sizeY;
    };
    
    /**
     * Executes the callbacks passed as arguments when a column begins to be
     * overlapped or stops being overlapped.
     *
     * @param  {Function} start_callback Function executed when a new column
     *  begins to be overlapped. The column is passed as first argument.
     * @param  {Function} stop_callback Function executed when a column stops
     *  being overlapped. The column is passed as first argument.
     * @method on_overlapped_column_change
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.on_overlapped_column_change = function (start_callback, stop_callback) {
        if (!this.colliders_data.length) {
            return this;
        }
        var cols = this.get_targeted_columns(
            this.colliders_data[0].el.data.col
        );
        
        var last_n_cols = this.last_cols.length;
        var n_cols = cols.length;
        var i;
        
        for (i = 0; i < n_cols; i++) {
            if (jQuery.inArray(cols[i], this.last_cols) === -1) {
                (start_callback || jQuery.noop).call(this, cols[i]);
            }
        }
        
        for (i = 0; i < last_n_cols; i++) {
            if (jQuery.inArray(this.last_cols[i], cols) === -1) {
                (stop_callback || jQuery.noop).call(this, this.last_cols[i]);
            }
        }
        
        this.last_cols = cols;
        
        return this;
    };
    
    /**
     * Executes the callbacks passed as arguments when a row starts to be
     * overlapped or stops being overlapped.
     *
     * @param  {Function} start_callback Function executed when a new row begins
     *  to be overlapped. The row is passed as first argument.
     * @param  {Function} end_callback Function executed when a row stops being
     *  overlapped. The row is passed as first argument.
     * @method on_overlapped_row_change
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.on_overlapped_row_change = function (start_callback, end_callback) {
        if (!this.colliders_data.length) {
            return this;
        }
        var rows = this.get_targeted_rows(this.colliders_data[0].el.data.row);
        var last_n_rows = this.last_rows.length;
        var n_rows = rows.length;
        var i;
        
        for (i = 0; i < n_rows; i++) {
            if (jQuery.inArray(rows[i], this.last_rows) === -1) {
                (start_callback || jQuery.noop).call(this, rows[i]);
            }
        }
        
        for (i = 0; i < last_n_rows; i++) {
            if (jQuery.inArray(this.last_rows[i], rows) === -1) {
                (end_callback || jQuery.noop).call(this, this.last_rows[i]);
            }
        }
        
        this.last_rows = rows;
    };
    
    /**
     * Sets the current position of the player
     *
     * @param  {Number} col
     * @param  {Number} row
     * @param  {Boolean} no_player
     * @method set_player
     * @return {object}
     */
    fn.set_player = function (col, row, no_player) {
        var self = this;
        if (!no_player) {
            this.empty_cells_player_occupies();
        }
        var cell = !no_player ? self.colliders_data[0].el.data : {col: col};
        var to_col = cell.col;
        var to_row = row || cell.row;
        
        this.player_grid_data = {
            col:   to_col,
            row:   to_row,
            sizeY: this.player_grid_data.sizeY,
            sizeX: this.player_grid_data.sizeX
        };
        
        this.cells_occupied_by_player = this.get_cells_occupied(
            this.player_grid_data
        );
        
        var jQueryoverlapped_widgets = this.get_widgets_overlapped(
            this.player_grid_data
        );
        
        var constraints = this.widgets_constraints(jQueryoverlapped_widgets);
        
        this.manage_movements(constraints.can_go_up, to_col, to_row);
        this.manage_movements(constraints.can_not_go_up, to_col, to_row);
        
        /* if there is not widgets overlapping in the new player position,
         * update the new placeholder position. */
        if (!jQueryoverlapped_widgets.length) {
            var pp = this.can_go_player_up(this.player_grid_data);
            if (pp !== false) {
                to_row = pp;
            }
            this.set_placeholder(to_col, to_row);
        }
        
        return {
            col: to_col,
            row: to_row
        };
    };
    
    /**
     * See which of the widgets in the jQuerywidgets param collection can go to
     * a upper row and which not.
     *
     * @method widgets_contraints
     * @param  {jQuery} jQuerywidgets A jQuery wrapped collection of
     * HTMLElements.
     * @return {object} Returns a literal Object with two keys: `can_go_up` &
     * `can_not_go_up`. Each contains a set of HTMLElements.
     */
    fn.widgets_constraints = function (jQuerywidgets) {
        var jQuerywidgets_can_go_up = jQuery([]);
        var jQuerywidgets_can_not_go_up;
        var wgd_can_go_up = [];
        var wgd_can_not_go_up = [];
        
        jQuerywidgets.each(
            jQuery.proxy(
                function (i, w) {
                    var jQueryw = jQuery(w);
                    var wgd = jQueryw.coords().grid;
                    if (this.can_go_widget_up(wgd)) {
                        jQuerywidgets_can_go_up = jQuerywidgets_can_go_up.add(jQueryw);
                        wgd_can_go_up.push(wgd);
                    } else {
                        wgd_can_not_go_up.push(wgd);
                    }
                }, this
            )
        );
        
        jQuerywidgets_can_not_go_up = jQuerywidgets.not(jQuerywidgets_can_go_up);
        
        return {
            can_go_up:     Gridster.sort_by_row_asc(wgd_can_go_up),
            can_not_go_up: Gridster.sort_by_row_desc(wgd_can_not_go_up)
        };
    };
    
    /**
     * Sorts an Array of grid coords objects (representing the grid coords of
     * each widget) in descending way.
     *
     * @method manage_movements
     * @param  {jQuery} jQuerywidgets A jQuery collection of HTMLElements
     *  representing the widgets you want to move.
     * @param  {Number} to_col The column to which we want to move the widgets.
     * @param  {Number} to_row The row to which we want to move the widgets.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.manage_movements = function (jQuerywidgets, to_col, to_row) {
        jQuery.each(
            jQuerywidgets, jQuery.proxy(
                function (i, w) {
                    var wgd = w;
                    var jQueryw = wgd.el;
                    
                    var can_go_widget_up = this.can_go_widget_up(wgd);
                    
                    if (can_go_widget_up) {
                        //target CAN go up
                        //so move widget up
                        this.move_widget_to(jQueryw, can_go_widget_up);
                        this.set_placeholder(to_col, can_go_widget_up + wgd.sizeY);
                        
                    } else {
                        //target can't go up
                        var can_go_player_up = this.can_go_player_up(
                            this.player_grid_data
                        );
                        
                        if (!can_go_player_up) {
                            // target can't go up
                            // player cant't go up
                            // so we need to move widget down to a position that dont
                            // overlaps player
                            var y = (to_row + this.player_grid_data.sizeY) - wgd.row;
                            
                            this.move_widget_down(jQueryw, y);
                            this.set_placeholder(to_col, to_row);
                        }
                    }
                }, this
            )
        );
        
        return this;
    };
    
    /**
     * Determines if there is a widget in the row and col given. Or if the
     * HTMLElement passed as first argument is the player.
     *
     * @method is_player
     * @param  {Number|HTMLElement} col_or_el A jQuery wrapped collection of
     * HTMLElements.
     * @param  {Number} [row] The column to which we want to move the widgets.
     * @return {Boolean} Returns true or false.
     */
    fn.is_player = function (col_or_el, row) {
        if (row && !this.gridmap[col_or_el]) {
            return false;
        }
        var jQueryw = row ? this.gridmap[col_or_el][row] : col_or_el;
        return jQueryw && (jQueryw.is(this.jQueryplayer) || jQueryw.is(this.jQueryhelper));
    };
    
    /**
     * Determines if the widget that is being dragged is currently over the row
     * and col given.
     *
     * @method is_player_in
     * @param  {Number} col The column to check.
     * @param  {Number} row The row to check.
     * @return {Boolean} Returns true or false.
     */
    fn.is_player_in = function (col, row) {
        var c = this.cells_occupied_by_player || {};
        return jQuery.inArray(col, c.cols) >= 0 && jQuery.inArray(row, c.rows) >= 0;
    };
    
    /**
     * Determines if the placeholder is currently over the row and col given.
     *
     * @method is_placeholder_in
     * @param  {Number} col The column to check.
     * @param  {Number} row The row to check.
     * @return {Boolean} Returns true or false.
     */
    fn.is_placeholder_in = function (col, row) {
        var c = this.cells_occupied_by_placeholder || {};
        return this.is_placeholder_in_col(col) && jQuery.inArray(row, c.rows) >= 0;
    };
    
    /**
     * Determines if the placeholder is currently over the column given.
     *
     * @method is_placeholder_in_col
     * @param  {Number} col The column to check.
     * @return {Boolean} Returns true or false.
     */
    fn.is_placeholder_in_col = function (col) {
        var c = this.cells_occupied_by_placeholder || [];
        return jQuery.inArray(col, c.cols) >= 0;
    };
    
    /**
     * Determines if the cell represented by col and row params is empty.
     *
     * @method is_empty
     * @param  {Number} col The column to check.
     * @param  {Number} row The row to check.
     * @return {Boolean} Returns true or false.
     */
    fn.is_empty = function (col, row) {
        if (typeof this.gridmap[col] !== 'undefined') {
            if (typeof this.gridmap[col][row] !== 'undefined'
                && this.gridmap[col][row] === false
            ) {
                return true;
            }
            return false;
        }
        return true;
    };
    
    /**
     * Determines if the cell represented by col and row params is occupied.
     *
     * @method is_occupied
     * @param  {Number} col The column to check.
     * @param  {Number} row The row to check.
     * @return {Boolean} Returns true or false.
     */
    fn.is_occupied = function (col, row) {
        if (!this.gridmap[col]) {
            return false;
        }
        
        if (this.gridmap[col][row]) {
            return true;
        }
        return false;
    };
    
    /**
     * Determines if there is a widget in the cell represented by col/row params.
     *
     * @method is_widget
     * @param  {Number} col The column to check.
     * @param  {Number} row The row to check.
     * @return {Boolean|HTMLElement} Returns false if there is no widget,
     * else returns the jQuery HTMLElement
     */
    fn.is_widget = function (col, row) {
        var cell = this.gridmap[col];
        if (!cell) {
            return false;
        }
        
        cell = cell[row];
        
        if (cell) {
            return cell;
        }
        
        return false;
    };
    
    /**
     * Determines if there is a widget in the cell represented by col/row
     * params and if this is under the widget that is being dragged.
     *
     * @method is_widget_under_player
     * @param  {Number} col The column to check.
     * @param  {Number} row The row to check.
     * @return {Boolean} Returns true or false.
     */
    fn.is_widget_under_player = function (col, row) {
        if (this.is_widget(col, row)) {
            return this.is_player_in(col, row);
        }
        return false;
    };
    
    /**
     * Get widgets overlapping with the player or with the object passed
     * representing the grid cells.
     *
     * @method get_widgets_under_player
     * @return {HTMLElement} Returns a jQuery collection of HTMLElements
     */
    fn.get_widgets_under_player = function (cells) {
        cells || (cells = this.cells_occupied_by_player || {cols: [], rows: []});
        var jQuerywidgets = jQuery([]);
        
        jQuery.each(
            cells.cols, jQuery.proxy(
                function (i, col) {
                    jQuery.each(
                        cells.rows, jQuery.proxy(
                            function (i, row) {
                                if (this.is_widget(col, row)) {
                                    jQuerywidgets = jQuerywidgets.add(this.gridmap[col][row]);
                                }
                            }, this
                        )
                    );
                }, this
            )
        );
        
        return jQuerywidgets;
    };
    
    /**
     * Put placeholder at the row and column specified.
     *
     * @method set_placeholder
     * @param  {Number} col The column to which we want to move the
     *  placeholder.
     * @param  {Number} row The row to which we want to move the
     *  placeholder.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.set_placeholder = function (col, row) {
        var phgd = jQuery.extend({}, this.placeholder_grid_data);
        var jQuerynexts = this.widgets_below(
            {
                col:   phgd.col,
                row:   phgd.row,
                sizeY: phgd.sizeY,
                sizeX: phgd.sizeX
            }
        );
        
        // Prevents widgets go out of the grid
        var right_col = (col + phgd.sizeX - 1);
        if (right_col > this.cols) {
            col = col - (right_col - col);
        }
        
        var moved_down = this.placeholder_grid_data.row < row;
        var changed_column = this.placeholder_grid_data.col !== col;
        
        this.placeholder_grid_data.col = col;
        this.placeholder_grid_data.row = row;
        
        this.cells_occupied_by_placeholder = this.get_cells_occupied(
            this.placeholder_grid_data
        );
        
        this.jQuerypreview_holder.attr(
            {
                'data-row': row,
                'data-col': col
            }
        );
        
        if (moved_down || changed_column) {
            jQuerynexts.each(
                jQuery.proxy(
                    function (i, widget) {
                        this.move_widget_up(
                            jQuery(widget), this.placeholder_grid_data.col - col + phgd.sizeY
                        );
                    }, this
                )
            );
        }
        
        var jQuerywidgets_under_ph = this.get_widgets_under_player(
            this.cells_occupied_by_placeholder
        );
        
        if (jQuerywidgets_under_ph.length) {
            jQuerywidgets_under_ph.each(
                jQuery.proxy(
                    function (i, widget) {
                        var jQueryw = jQuery(widget);
                        this.move_widget_down(
                            jQueryw, row + phgd.sizeY - jQueryw.data('coords').grid.row
                        );
                    }, this
                )
            );
        }
        
    };
    
    /**
     * Determines whether the player can move to a position above.
     *
     * @method can_go_player_up
     * @param  {Object} widget_grid_data The actual grid coords object of the
     *  player.
     * @return {Number|Boolean} If the player can be moved to an upper row
     *  returns the row number, else returns false.
     */
    fn.can_go_player_up = function (widget_grid_data) {
        var p_bottom_row = widget_grid_data.row + widget_grid_data.sizeY - 1;
        var result = true;
        var upper_rows = [];
        var min_row = 10000;
        var jQuerywidgets_under_player = this.get_widgets_under_player();
        
        /* generate an array with columns as index and array with upper rows
         * empty as value */
        this.for_each_column_occupied(
            widget_grid_data, function (tcol) {
                var grid_col = this.gridmap[tcol];
                var r = p_bottom_row + 1;
                upper_rows[tcol] = [];
                
                while (--r > 0) {
                    if (this.is_empty(tcol, r) || this.is_player(tcol, r)
                        || this.is_widget(tcol, r)
                        && grid_col[r].is(jQuerywidgets_under_player)
                    ) {
                        upper_rows[tcol].push(r);
                        min_row = r < min_row ? r : min_row;
                    } else {
                        break;
                    }
                }
                
                if (upper_rows[tcol].length === 0) {
                    result = false;
                    return true; //break
                }
                
                upper_rows[tcol].sort(
                    function (a, b) {
                        return a - b;
                    }
                );
            }
        );
        
        if (!result) {
            return false;
        }
        
        return this.get_valid_rows(widget_grid_data, upper_rows, min_row);
    };
    
    /**
     * Determines whether a widget can move to a position above.
     *
     * @method can_go_widget_up
     * @param  {Object} widget_grid_data The actual grid coords object of the
     *  widget we want to check.
     * @return {Number|Boolean} If the widget can be moved to an upper row
     *  returns the row number, else returns false.
     */
    fn.can_go_widget_up = function (widget_grid_data) {
        var p_bottom_row = widget_grid_data.row + widget_grid_data.sizeY - 1;
        var result = true;
        var upper_rows = [];
        var min_row = 10000;
        
        /* generate an array with columns as index and array with topmost rows
         * empty as value */
        this.for_each_column_occupied(
            widget_grid_data, function (tcol) {
                var grid_col = this.gridmap[tcol];
                upper_rows[tcol] = [];
                
                var r = p_bottom_row + 1;
                // iterate over each row
                while (--r > 0) {
                    if (this.is_widget(tcol, r) && !this.is_player_in(tcol, r)) {
                        if (!grid_col[r].is(widget_grid_data.el)) {
                            break;
                        }
                    }
                    
                    if (!this.is_player(tcol, r) && !this.is_placeholder_in(tcol, r) && !this.is_player_in(tcol, r)) {
                        upper_rows[tcol].push(r);
                    }
                    
                    if (r < min_row) {
                        min_row = r;
                    }
                }
                
                if (upper_rows[tcol].length === 0) {
                    result = false;
                    return true; //break
                }
                
                upper_rows[tcol].sort(
                    function (a, b) {
                        return a - b;
                    }
                );
            }
        );
        
        if (!result) {
            return false;
        }
        
        return this.get_valid_rows(widget_grid_data, upper_rows, min_row);
    };
    
    /**
     * Search a valid row for the widget represented by `widget_grid_data' in
     * the `upper_rows` array. Iteration starts from row specified in `min_row`.
     *
     * @method get_valid_rows
     * @param  {Object} widget_grid_data The actual grid coords object of the
     *  player.
     * @param  {Array} upper_rows An array with columns as index and arrays
     *  of valid rows as values.
     * @param  {Number} min_row The upper row from which the iteration will start.
     * @return {Number|Boolean} Returns the upper row valid from the `upper_rows`
     *  for the widget in question.
     */
    fn.get_valid_rows = function (widget_grid_data, upper_rows, min_row) {
        var p_top_row = widget_grid_data.row;
        var p_bottom_row = widget_grid_data.row + widget_grid_data.sizeY - 1;
        var sizeY = widget_grid_data.sizeY;
        var r = min_row - 1;
        var valid_rows = [];
        
        while (++r <= p_bottom_row) {
            var common = true;
            jQuery.each(
                upper_rows, function (col, rows) {
                    if (jQuery.isArray(rows) && jQuery.inArray(r, rows) === -1) {
                        common = false;
                    }
                }
            );
            
            if (common === true) {
                valid_rows.push(r);
                if (valid_rows.length === sizeY) {
                    break;
                }
            }
        }
        
        var new_row = false;
        if (sizeY === 1) {
            if (valid_rows[0] !== p_top_row) {
                new_row = valid_rows[0] || false;
            }
        } else {
            if (valid_rows[0] !== p_top_row) {
                new_row = this.get_consecutive_numbers_index(
                    valid_rows, sizeY
                );
            }
        }
        
        return new_row;
    };
    
    fn.get_consecutive_numbers_index = function (arr, sizeY) {
        var max = arr.length;
        var result = [];
        var first = true;
        var prev = -1; // or null?
        
        for (var i = 0; i < max; i++) {
            if (first || arr[i] === prev + 1) {
                result.push(i);
                if (result.length === sizeY) {
                    break;
                }
                first = false;
            } else {
                result = [];
                first = true;
            }
            
            prev = arr[i];
        }
        
        return result.length >= sizeY ? arr[result[0]] : false;
    };
    
    /**
     * Get widgets overlapping with the player.
     *
     * @method get_widgets_overlapped
     * @return {jQuery} Returns a jQuery collection of HTMLElements.
     */
    fn.get_widgets_overlapped = function () {
        var jQueryw;
        var jQuerywidgets = jQuery([]);
        var used = [];
        var rows_from_bottom = this.cells_occupied_by_player.rows.slice(0);
        rows_from_bottom.reverse();
        
        jQuery.each(
            this.cells_occupied_by_player.cols, jQuery.proxy(
                function (i, col) {
                    jQuery.each(
                        rows_from_bottom, jQuery.proxy(
                            function (i, row) {
                                // if there is a widget in the player position
                                if (!this.gridmap[col]) {
                                    return true;
                                } //next iteration
                                var jQueryw = this.gridmap[col][row];
                                if (this.is_occupied(col, row) && !this.is_player(jQueryw)
                                    && jQuery.inArray(jQueryw, used) === -1
                                ) {
                                    jQuerywidgets = jQuerywidgets.add(jQueryw);
                                    used.push(jQueryw);
                                }
                                
                            }, this
                        )
                    );
                }, this
            )
        );
        
        return jQuerywidgets;
    };
    
    /**
     * This callback is executed when the player begins to collide with a column.
     *
     * @method on_start_overlapping_column
     * @param  {Number} col The collided column.
     * @return {jQuery} Returns a jQuery collection of HTMLElements.
     */
    fn.on_start_overlapping_column = function (col) {
        this.set_player(col, false);
    };
    
    /**
     * A callback executed when the player begins to collide with a row.
     *
     * @method on_start_overlapping_row
     * @param  {Number} row The collided row.
     * @return {jQuery} Returns a jQuery collection of HTMLElements.
     */
    fn.on_start_overlapping_row = function (row) {
        this.set_player(false, row);
    };
    
    /**
     * A callback executed when the the player ends to collide with a column.
     *
     * @method on_stop_overlapping_column
     * @param  {Number} col The collided row.
     * @return {jQuery} Returns a jQuery collection of HTMLElements.
     */
    fn.on_stop_overlapping_column = function (col) {
        this.set_player(col, false);
        
        var self = this;
        this.for_each_widget_below(
            col, this.cells_occupied_by_player.rows[0],
            function (tcol, trow) {
                self.move_widget_up(this, self.player_grid_data.sizeY);
            }
        );
    };
    
    /**
     * This callback is executed when the player ends to collide with a row.
     *
     * @method on_stop_overlapping_row
     * @param  {Number} row The collided row.
     * @return {jQuery} Returns a jQuery collection of HTMLElements.
     */
    fn.on_stop_overlapping_row = function (row) {
        this.set_player(false, row);
        
        var self = this;
        var cols = this.cells_occupied_by_player.cols;
        for (var c = 0, cl = cols.length; c < cl; c++) {
            this.for_each_widget_below(
                cols[c], row, function (tcol, trow) {
                    self.move_widget_up(this, self.player_grid_data.sizeY);
                }
            );
        }
    };
    
    /**
     * Move a widget to a specific row. The cell or cells must be empty.
     * If the widget has widgets below, all of these widgets will be moved also
     * if they can.
     *
     * @method move_widget_to
     * @param  {HTMLElement} jQuerywidget The jQuery wrapped HTMLElement of the
     * widget is going to be moved.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.move_widget_to = function (jQuerywidget, row) {
        var self = this;
        var widget_grid_data = jQuerywidget.coords().grid;
        var diff = row - widget_grid_data.row;
        var jQuerynext_widgets = this.widgets_below(jQuerywidget);
        
        var can_move_to_new_cell = this.can_move_to(
            widget_grid_data, widget_grid_data.col, row, jQuerywidget
        );
        
        if (can_move_to_new_cell === false) {
            return false;
        }
        
        this.remove_from_gridmap(widget_grid_data);
        widget_grid_data.row = row;
        this.add_to_gridmap(widget_grid_data);
        jQuerywidget.attr('data-row', row);
        this.jQuerychanged = this.jQuerychanged.add(jQuerywidget);
        
        jQuerynext_widgets.each(
            function (i, widget) {
                var jQueryw = jQuery(widget);
                var wgd = jQueryw.coords().grid;
                var can_go_up = self.can_go_widget_up(wgd);
                if (can_go_up && can_go_up !== wgd.row) {
                    self.move_widget_to(jQueryw, can_go_up);
                }
            }
        );
        
        return this;
    };
    
    /**
     * Move up the specified widget and all below it.
     *
     * @method move_widget_up
     * @param  {HTMLElement} jQuerywidget The widget you want to move.
     * @param  {Number} [y_units] The number of cells that the widget has to move.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.move_widget_up = function (jQuerywidget, y_units) {
        var el_grid_data = jQuerywidget.coords().grid;
        var actual_row = el_grid_data.row;
        var moved = [];
        var can_go_up = true;
        y_units || (y_units = 1);
        
        if (!this.can_go_up(jQuerywidget)) {
            return false;
        } //break;
        
        this.for_each_column_occupied(
            el_grid_data, function (col) {
                // can_go_up
                if (jQuery.inArray(jQuerywidget, moved) === -1) {
                    var widget_grid_data = jQuerywidget.coords().grid;
                    var next_row = actual_row - y_units;
                    next_row = this.can_go_up_to_row(
                        widget_grid_data, col, next_row
                    );
                    
                    if (!next_row) {
                        return true;
                    }
                    
                    var jQuerynext_widgets = this.widgets_below(jQuerywidget);
                    
                    this.remove_from_gridmap(widget_grid_data);
                    widget_grid_data.row = next_row;
                    this.add_to_gridmap(widget_grid_data);
                    jQuerywidget.attr('data-row', widget_grid_data.row);
                    this.jQuerychanged = this.jQuerychanged.add(jQuerywidget);
                    
                    moved.push(jQuerywidget);
                    
                    jQuerynext_widgets.each(
                        jQuery.proxy(
                            function (i, widget) {
                                this.move_widget_up(jQuery(widget), y_units);
                            }, this
                        )
                    );
                }
            }
        );
        
    };
    
    /**
     * Move down the specified widget and all below it.
     *
     * @method move_widget_down
     * @param  {jQuery} jQuerywidget The jQuery object representing the widget
     *  you want to move.
     * @param  {Number} y_units The number of cells that the widget has to move.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.move_widget_down = function (jQuerywidget, y_units) {
        var el_grid_data, actual_row, moved, y_diff;
        
        if (y_units <= 0) {
            return false;
        }
        
        el_grid_data = jQuerywidget.coords().grid;
        actual_row = el_grid_data.row;
        moved = [];
        y_diff = y_units;
        
        if (!jQuerywidget) {
            return false;
        }
        
        if (jQuery.inArray(jQuerywidget, moved) === -1) {
            
            var widget_grid_data = jQuerywidget.coords().grid;
            var next_row = actual_row + y_units;
            var jQuerynext_widgets = this.widgets_below(jQuerywidget);
            
            this.remove_from_gridmap(widget_grid_data);
            
            jQuerynext_widgets.each(
                jQuery.proxy(
                    function (i, widget) {
                        var jQueryw = jQuery(widget);
                        var wd = jQueryw.coords().grid;
                        var tmp_y = this.displacement_diff(
                            wd, widget_grid_data, y_diff
                        );
                        
                        if (tmp_y > 0) {
                            this.move_widget_down(jQueryw, tmp_y);
                        }
                    }, this
                )
            );
            
            widget_grid_data.row = next_row;
            this.update_widget_position(widget_grid_data, jQuerywidget);
            jQuerywidget.attr('data-row', widget_grid_data.row);
            this.jQuerychanged = this.jQuerychanged.add(jQuerywidget);
            
            moved.push(jQuerywidget);
        }
    };
    
    /**
     * Check if the widget can move to the specified row, else returns the
     * upper row possible.
     *
     * @method can_go_up_to_row
     * @param  {Number} widget_grid_data The current grid coords object of the
     *  widget.
     * @param  {Number} col The target column.
     * @param  {Number} row The target row.
     * @return {Boolean|Number} Returns the row number if the widget can move
     *  to the target position, else returns false.
     */
    fn.can_go_up_to_row = function (widget_grid_data, col, row) {
        var ga = this.gridmap;
        var result = true;
        var urc = []; // upper_rows_in_columns
        var actual_row = widget_grid_data.row;
        var r;
        
        /* generate an array with columns as index and array with
         * upper rows empty in the column */
        this.for_each_column_occupied(
            widget_grid_data, function (tcol) {
                var grid_col = ga[tcol];
                urc[tcol] = [];
                
                r = actual_row;
                while (r--) {
                    if (this.is_empty(tcol, r) && !this.is_placeholder_in(tcol, r)
                    ) {
                        urc[tcol].push(r);
                    } else {
                        break;
                    }
                }
                
                if (!urc[tcol].length) {
                    result = false;
                    return true;
                }
                
            }
        );
        
        if (!result) {
            return false;
        }
        
        /* get common rows starting from upper position in all the columns
         * that widget occupies */
        r = row;
        for (r = 1; r < actual_row; r++) {
            var common = true;
            
            for (var uc = 0, ucl = urc.length; uc < ucl; uc++) {
                if (urc[uc] && jQuery.inArray(r, urc[uc]) === -1) {
                    common = false;
                }
            }
            
            if (common === true) {
                result = r;
                break;
            }
        }
        
        return result;
    };
    
    fn.displacement_diff = function (widget_grid_data, parent_bgd, y_units) {
        var actual_row = widget_grid_data.row;
        var diffs = [];
        var parent_max_y = parent_bgd.row + parent_bgd.sizeY;
        
        this.for_each_column_occupied(
            widget_grid_data, function (col) {
                var temp_y_units = 0;
                
                for (var r = parent_max_y; r < actual_row; r++) {
                    if (this.is_empty(col, r)) {
                        temp_y_units = temp_y_units + 1;
                    }
                }
                
                diffs.push(temp_y_units);
            }
        );
        
        var max_diff = Math.max.apply(Math, diffs);
        y_units = (y_units - max_diff);
        
        return y_units > 0 ? y_units : 0;
    };
    
    /**
     * Get widgets below a widget.
     *
     * @method widgets_below
     * @param  {HTMLElement} jQueryel The jQuery wrapped HTMLElement.
     * @return {jQuery} A jQuery collection of HTMLElements.
     */
    fn.widgets_below = function (jQueryel) {
        var el_grid_data = jQuery.isPlainObject(jQueryel) ? jQueryel : jQueryel.coords().grid;
        var self = this;
        var ga = this.gridmap;
        var next_row = el_grid_data.row + el_grid_data.sizeY - 1;
        var jQuerynexts = jQuery([]);
        
        this.for_each_column_occupied(
            el_grid_data, function (col) {
                self.for_each_widget_below(
                    col, next_row, function (tcol, trow) {
                        if (!self.is_player(this) && jQuery.inArray(this, jQuerynexts) === -1) {
                            jQuerynexts = jQuerynexts.add(this);
                            return true; // break
                        }
                    }
                );
            }
        );
        
        return Gridster.sort_by_row_asc(jQuerynexts);
    };
    
    /**
     * Update the array of mapped positions with the new player position.
     *
     * @method set_cells_player_occupies
     * @param  {Number} col The new player col.
     * @param  {Number} col The new player row.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.set_cells_player_occupies = function (col, row) {
        this.remove_from_gridmap(this.placeholder_grid_data);
        this.placeholder_grid_data.col = col;
        this.placeholder_grid_data.row = row;
        this.add_to_gridmap(this.placeholder_grid_data, this.jQueryplayer);
        return this;
    };
    
    /**
     * Remove from the array of mapped positions the reference to the player.
     *
     * @method empty_cells_player_occupies
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.empty_cells_player_occupies = function () {
        this.remove_from_gridmap(this.placeholder_grid_data);
        return this;
    };
    
    fn.can_go_up = function (jQueryel) {
        var el_grid_data = jQueryel.coords().grid;
        var initial_row = el_grid_data.row;
        var prev_row = initial_row - 1;
        var ga = this.gridmap;
        var upper_rows_by_column = [];
        
        var result = true;
        if (initial_row === 1) {
            return false;
        }
        
        this.for_each_column_occupied(
            el_grid_data, function (col) {
                var jQueryw = this.is_widget(col, prev_row);
                
                if (this.is_occupied(col, prev_row)
                    || this.is_player(col, prev_row)
                    || this.is_placeholder_in(col, prev_row)
                    || this.is_player_in(col, prev_row)
                ) {
                    result = false;
                    return true; //break
                }
            }
        );
        
        return result;
    };
    
    /**
     * Check if it's possible to move a widget to a specific col/row. It takes
     * into account the dimensions (`sizeY` and `sizeX` attrs. of the grid
     *  coords object) the widget occupies.
     *
     * @method can_move_to
     * @param  {Object} widget_grid_data The grid coords object that represents
     *  the widget.
     * @param  {Object} col The col to check.
     * @param  {Object} row The row to check.
     * @param  {Number} [max_row] The max row allowed.
     * @return {Boolean} Returns true if all cells are empty, else return false.
     */
    fn.can_move_to = function (widget_grid_data, col, row, max_row) {
        var ga = this.gridmap;
        var jQueryw = widget_grid_data.el;
        var future_wd = {
            sizeY: widget_grid_data.sizeY,
            sizeX: widget_grid_data.sizeX,
            col:   col,
            row:   row
        };
        var result = true;
        
        //Prevents widgets go out of the grid
        var right_col = col + widget_grid_data.sizeX - 1;
        if (right_col > this.cols) {
            return false;
        }
        
        if (max_row && max_row < row + widget_grid_data.sizeY - 1) {
            return false;
        }
        
        this.for_each_cell_occupied(
            future_wd, function (tcol, trow) {
                var jQuerytw = this.is_widget(tcol, trow);
                if (jQuerytw && (!widget_grid_data.el || jQuerytw.is(jQueryw))) {
                    result = false;
                }
            }
        );
        
        return result;
    };
    
    /**
     * Given the leftmost column returns all columns that are overlapping
     *  with the player.
     *
     * @method get_targeted_columns
     * @param  {Number} [from_col] The leftmost column.
     * @return {Array} Returns an array with column numbers.
     */
    fn.get_targeted_columns = function (from_col) {
        var max = (from_col || this.player_grid_data.col) +
            (this.player_grid_data.sizeX - 1);
        var cols = [];
        for (var col = from_col; col <= max; col++) {
            cols.push(col);
        }
        return cols;
    };
    
    /**
     * Given the upper row returns all rows that are overlapping with the player.
     *
     * @method get_targeted_rows
     * @param  {Number} [from_row] The upper row.
     * @return {Array} Returns an array with row numbers.
     */
    fn.get_targeted_rows = function (from_row) {
        var max = (from_row || this.player_grid_data.row) +
            (this.player_grid_data.sizeY - 1);
        var rows = [];
        for (var row = from_row; row <= max; row++) {
            rows.push(row);
        }
        return rows;
    };
    
    /**
     * Get all columns and rows that a widget occupies.
     *
     * @method get_cells_occupied
     * @param  {Object} el_grid_data The grid coords object of the widget.
     * @return {Object} Returns an object like `{ cols: [], rows: []}`.
     */
    fn.get_cells_occupied = function (el_grid_data) {
        var cells = {cols: [], rows: []};
        var i;
        if (arguments[1] instanceof jQuery) {
            el_grid_data = arguments[1].coords().grid;
        }
        
        for (i = 0; i < el_grid_data.sizeX; i++) {
            var col = el_grid_data.col + i;
            cells.cols.push(col);
        }
        
        for (i = 0; i < el_grid_data.sizeY; i++) {
            var row = el_grid_data.row + i;
            cells.rows.push(row);
        }
        
        return cells;
    };
    
    /**
     * Iterate over the cells occupied by a widget executing a function for
     * each one.
     *
     * @method for_each_cell_occupied
     * @param  {Object} el_grid_data The grid coords object that represents the
     *  widget.
     * @param  {Function} callback The function to execute on each column
     *  iteration. Column and row are passed as arguments.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.for_each_cell_occupied = function (grid_data, callback) {
        this.for_each_column_occupied(
            grid_data, function (col) {
                this.for_each_row_occupied(
                    grid_data, function (row) {
                        callback.call(this, col, row);
                    }
                );
            }
        );
        return this;
    };
    
    /**
     * Iterate over the columns occupied by a widget executing a function for
     * each one.
     *
     * @method for_each_column_occupied
     * @param  {Object} el_grid_data The grid coords object that represents
     *  the widget.
     * @param  {Function} callback The function to execute on each column
     *  iteration. The column number is passed as first argument.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.for_each_column_occupied = function (el_grid_data, callback) {
        for (var i = 0; i < el_grid_data.sizeX; i++) {
            var col = el_grid_data.col + i;
            callback.call(this, col, el_grid_data);
        }
    };
    
    /**
     * Iterate over the rows occupied by a widget executing a function for
     * each one.
     *
     * @method for_each_row_occupied
     * @param  {Object} el_grid_data The grid coords object that represents
     *  the widget.
     * @param  {Function} callback The function to execute on each column
     *  iteration. The row number is passed as first argument.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.for_each_row_occupied = function (el_grid_data, callback) {
        for (var i = 0; i < el_grid_data.sizeY; i++) {
            var row = el_grid_data.row + i;
            callback.call(this, row, el_grid_data);
        }
    };
    
    fn._traversing_widgets = function (type, direction, col, row, callback) {
        var ga = this.gridmap;
        if (!ga[col]) {
            return;
        }
        
        var cr, max;
        var action = type + '/' + direction;
        if (arguments[2] instanceof jQuery) {
            var el_grid_data = arguments[2].coords().grid;
            col = el_grid_data.col;
            row = el_grid_data.row;
            callback = arguments[3];
        }
        var matched = [];
        var trow = row;
        
        var methods = {
            'for_each/above': function () {
                while (trow--) {
                    if (trow > 0 && this.is_widget(col, trow)
                        && jQuery.inArray(ga[col][trow], matched) === -1
                    ) {
                        cr = callback.call(ga[col][trow], col, trow);
                        matched.push(ga[col][trow]);
                        if (cr) {
                            break;
                        }
                    }
                }
            },
            'for_each/below': function () {
                for (trow = row + 1, max = ga[col].length; trow < max; trow++) {
                    if (this.is_widget(col, trow)
                        && jQuery.inArray(ga[col][trow], matched) === -1
                    ) {
                        cr = callback.call(ga[col][trow], col, trow);
                        matched.push(ga[col][trow]);
                        if (cr) {
                            break;
                        }
                    }
                }
            }
        };
        
        if (methods[action]) {
            methods[action].call(this);
        }
    };
    
    /**
     * Iterate over each widget above the column and row specified.
     *
     * @method for_each_widget_above
     * @param  {Number} col The column to start iterating.
     * @param  {Number} row The row to start iterating.
     * @param  {Function} callback The function to execute on each widget
     *  iteration. The value of `this` inside the function is the jQuery
     *  wrapped HTMLElement.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.for_each_widget_above = function (col, row, callback) {
        this._traversing_widgets('for_each', 'above', col, row, callback);
        return this;
    };
    
    /**
     * Iterate over each widget below the column and row specified.
     *
     * @method for_each_widget_below
     * @param  {Number} col The column to start iterating.
     * @param  {Number} row The row to start iterating.
     * @param  {Function} callback The function to execute on each widget
     *  iteration. The value of `this` inside the function is the jQuery wrapped
     *  HTMLElement.
     * @return {Class} Returns the instance of the Gridster Class.
     */
    fn.for_each_widget_below = function (col, row, callback) {
        this._traversing_widgets('for_each', 'below', col, row, callback);
        return this;
    };
    
    /**
     * Returns the highest occupied cell in the grid.
     *
     * @method get_highest_occupied_cell
     * @return {Object} Returns an object with `col` and `row` numbers.
     */
    fn.get_highest_occupied_cell = function () {
        var r;
        var gm = this.gridmap;
        var rl = gm[1].length;
        var rows = [], cols = [];
        var row_in_col = [];
        for (var c = gm.length - 1; c >= 1; c--) {
            for (r = rl - 1; r >= 1; r--) {
                if (this.is_widget(c, r)) {
                    rows.push(r);
                    cols.push(c);
                    break;
                }
            }
        }
        
        return {
            col: Math.max.apply(Math, cols),
            row: Math.max.apply(Math, rows)
        };
    };
    
    fn.get_widgets_from = function (col, row) {
        var ga = this.gridmap;
        var jQuerywidgets = jQuery();
        
        if (col) {
            jQuerywidgets = jQuerywidgets.add(
                this.jQuerywidgets.filter(
                    function () {
                        var tcol = jQuery(this).attr('data-col');
                        return (tcol === col || tcol > col);
                    }
                )
            );
        }
        
        if (row) {
            jQuerywidgets = jQuerywidgets.add(
                this.jQuerywidgets.filter(
                    function () {
                        var trow = jQuery(this).attr('data-row');
                        return (trow === row || trow > row);
                    }
                )
            );
        }
        
        return jQuerywidgets;
    };
    
    /**
     * Set the current height of the parent grid.
     *
     * @method setDomGridHeight
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.setDomGridHeight = function (height) {
        if (typeof height === 'undefined') {
            var r = this.get_highest_occupied_cell().row;
            height = r * this.min_widget_height;
        }
        
        this.container_height = height;
        this.jQueryel.css('height', this.container_height);
        return this;
    };
    
    /**
     * Set the current width of the parent grid.
     *
     * @method setDomGridWidth
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.setDomGridWidth = function (cols) {
        if (typeof cols === 'undefined') {
            cols = this.get_highest_occupied_cell().col;
        }
        
        var max_cols = (this.options.autogrow_cols ? this.options.max_cols :
            this.cols);
        
        cols = Math.min(max_cols, Math.max(cols, this.options.min_cols));
        this.container_width = cols * this.min_widget_width;
        
        this.jQueryel.css('width', this.container_width);
        return this;
    };
    
    /**
     * It generates the neccessary styles to position the widgets.
     *
     * @method generate_stylesheet
     * @param  {Number} rows Number of columns.
     * @param  {Number} cols Number of rows.
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.generate_stylesheet = function (opts) {
        var styles = '';
        var max_sizeX = this.options.max_sizeX || this.cols;
        var max_rows = 0;
        var max_cols = 0;
        var i;
        var rules;
        
        opts || (opts = {});
        opts.cols || (opts.cols = this.cols);
        opts.rows || (opts.rows = this.rows);
        opts.namespace || (opts.namespace = this.options.namespace);
        opts.widgetBaseDimensions ||
        (opts.widgetBaseDimensions = this.options.widgetBaseDimensions);
        opts.widgetMargins ||
        (opts.widgetMargins = this.options.widgetMargins);
        opts.min_widget_width = (opts.widgetMargins[0] * 2) +
            opts.widgetBaseDimensions[0];
        opts.min_widget_height = (opts.widgetMargins[1] * 2) +
            opts.widgetBaseDimensions[1];
        
        // don't duplicate stylesheets for the same configuration
        var serialized_opts = jQuery.param(opts);
        if (jQuery.inArray(serialized_opts, Gridster.generated_stylesheets) >= 0) {
            return false;
        }
        
        this.generated_stylesheets.push(serialized_opts);
        Gridster.generated_stylesheets.push(serialized_opts);
        
        /* generate CSS styles for cols */
        for (i = opts.cols; i >= 0; i--) {
            styles += (opts.namespace + ' [data-col="' + (i + 1) + '"] { left:' +
            ((i * opts.widgetBaseDimensions[0]) +
            (i * opts.widgetMargins[0]) +
            ((i + 1) * opts.widgetMargins[0])) + 'px; }\n');
        }
        
        /* generate CSS styles for rows */
        for (i = opts.rows + 20; i >= 0; i--) {
            styles += (opts.namespace + ' [data-row="' + (i + 1) + '"] { top:' +
            ((i * opts.widgetBaseDimensions[1]) +
            (i * opts.widgetMargins[1]) +
            ((i + 1) * opts.widgetMargins[1]) ) + 'px; }\n');
        }
        
        for (var y = 1; y <= opts.rows; y++) {
            styles += (opts.namespace + ' [data-sizey="' + y + '"] { height:' +
            (y * opts.widgetBaseDimensions[1] +
            (y - 1) * (opts.widgetMargins[1] * 2)) + 'px; }\n');
        }
        
        for (var x = 1; x <= max_sizeX; x++) {
            styles += (opts.namespace + ' [data-sizex="' + x + '"] { width:' +
            (x * opts.widgetBaseDimensions[0] +
            (x - 1) * (opts.widgetMargins[0] * 2)) + 'px; }\n');
        }
        
        this.remove_style_tags();
        
        return this.add_style_tag(styles);
    };
    
    /**
     * Injects the given CSS as string to the head of the document.
     *
     * @method add_style_tag
     * @param  {String} css The styles to apply.
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.add_style_tag = function (css) {
        var d = document;
        var tag = d.createElement('style');
        
        d.getElementsByTagName('head')[0].appendChild(tag);
        tag.setAttribute('type', 'text/css');
        
        if (tag.styleSheet) {
            tag.styleSheet.cssText = css;
        } else {
            tag.appendChild(document.createTextNode(css));
        }
        
        this.jQuerystyle_tags = this.jQuerystyle_tags.add(tag);
        
        return this;
    };
    
    /**
     * Remove the style tag with the associated id from the head of the document
     *
     * @method remove_style_tag
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.remove_style_tags = function () {
        var all_styles = Gridster.generated_stylesheets;
        var ins_styles = this.generated_stylesheets;
        
        this.jQuerystyle_tags.remove();
        
        Gridster.generated_stylesheets = jQuery.map(
            all_styles, function (s) {
                if (jQuery.inArray(s, ins_styles) === -1) {
                    return s;
                }
            }
        );
    };
    
    /**
     * Generates a faux grid to collide with it when a widget is dragged and
     * detect row or column that we want to go.
     *
     * @method generate_faux_grid
     * @param  {Number} rows Number of columns.
     * @param  {Number} cols Number of rows.
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.generate_faux_grid = function (rows, cols) {
        this.faux_grid = [];
        this.gridmap = [];
        var col;
        var row;
        for (col = cols; col > 0; col--) {
            this.gridmap[col] = [];
            for (row = rows; row > 0; row--) {
                this.add_faux_cell(row, col);
            }
        }
        return this;
    };
    
    /**
     * Add cell to the faux grid.
     *
     * @method add_faux_cell
     * @param  {Number} row The row for the new faux cell.
     * @param  {Number} col The col for the new faux cell.
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.add_faux_cell = function (row, col) {
        var coords = jQuery(
            {
                left:         this.baseX + ((col - 1) * this.min_widget_width),
                top:          this.baseY + (row - 1) * this.min_widget_height,
                width:        this.min_widget_width,
                height:       this.min_widget_height,
                col:          col,
                row:          row,
                original_col: col,
                original_row: row
            }
        ).coords();
        
        if (!jQuery.isArray(this.gridmap[col])) {
            this.gridmap[col] = [];
        }
        
        this.gridmap[col][row] = false;
        this.faux_grid.push(coords);
        
        return this;
    };
    
    /**
     * Add rows to the faux grid.
     *
     * @method add_faux_rows
     * @param  {Number} rows The number of rows you want to add to the faux grid.
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.add_faux_rows = function (rows) {
        var actual_rows = this.rows;
        var max_rows = actual_rows + (rows || 1);
        
        for (var r = max_rows; r > actual_rows; r--) {
            for (var c = this.cols; c >= 1; c--) {
                this.add_faux_cell(r, c);
            }
        }
        
        this.rows = max_rows;
        
        if (this.options.autogenerate_stylesheet) {
            this.generate_stylesheet();
        }
        
        return this;
    };
    
    /**
     * Add cols to the faux grid.
     *
     * @method add_faux_cols
     * @param  {Number} cols The number of cols you want to add to the faux grid.
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.add_faux_cols = function (cols) {
        var actual_cols = this.cols;
        var max_cols = actual_cols + (cols || 1);
        max_cols = Math.min(max_cols, this.options.max_cols);
        
        for (var c = actual_cols + 1; c <= max_cols; c++) {
            for (var r = this.rows; r >= 1; r--) {
                this.add_faux_cell(r, c);
            }
        }
        
        this.cols = max_cols;
        
        if (this.options.autogenerate_stylesheet) {
            this.generate_stylesheet();
        }
        
        return this;
    };
    
    /**
     * Recalculates the offsets for the faux grid. You need to use it when
     * the browser is resized.
     *
     * @method recalculate_faux_grid
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.recalculate_faux_grid = function () {
        var aw = this.jQuerywrapper.width();
        this.baseX = (jQuery(window).width() - aw) / 2;
        this.baseY = this.jQuerywrapper.offset().top;
        
        jQuery.each(
            this.faux_grid, jQuery.proxy(
                function (i, coords) {
                    this.faux_grid[i] = coords.update(
                        {
                            left: this.baseX + (coords.data.col - 1) * this.min_widget_width,
                            top:  this.baseY + (coords.data.row - 1) * this.min_widget_height
                        }
                    );
                }, this
            )
        );
        
        return this;
    };
    
    /**
     * Get all widgets in the DOM and register them.
     *
     * @method get_widgets_from_DOM
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.get_widgets_from_DOM = function () {
        var widgets_coords = this.jQuerywidgets.map(
            jQuery.proxy(
                function (i, widget) {
                    var jQueryw = jQuery(widget);
                    return this.dom_to_coords(jQueryw);
                }, this
            )
        );
        
        widgets_coords = Gridster.sort_by_row_and_col_asc(widgets_coords);
        
        var changes = jQuery(widgets_coords).map(
            jQuery.proxy(
                function (i, wgd) {
                    return this.registerWidget(wgd) || null;
                }, this
            )
        );
        
        if (changes.length) {
            this.jQueryel.trigger('gridster:positionschanged');
        }
        
        return this;
    };
    
    /**
     * Calculate columns and rows to be set based on the configuration
     *  parameters, grid dimensions, etc ...
     *
     * @method generate_grid_and_stylesheet
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.generate_grid_and_stylesheet = function () {
        var aw = this.jQuerywrapper.width();
        
        var max_cols = this.options.max_cols;
        
        var cols = Math.floor(aw / this.min_widget_width) +
            this.options.extra_cols;
        
        var actual_cols = this.jQuerywidgets.map(
            function () {
                return jQuery(this).attr('data-col');
            }
        ).get();
        
        //needed to pass tests with phantomjs
        actual_cols.length || (actual_cols = [0]);
        
        var min_cols = Math.max.apply(Math, actual_cols);
        
        this.cols = Math.max(min_cols, cols, this.options.min_cols);
        
        if (max_cols !== Infinity && max_cols >= min_cols && max_cols < this.cols) {
            this.cols = max_cols;
        }
        
        // get all rows that could be occupied by the current widgets
        var max_rows = this.options.extra_rows;
        this.jQuerywidgets.each(
            function (i, w) {
                max_rows += (+jQuery(w).attr('data-sizey'));
            }
        );
        
        this.rows = Math.max(max_rows, this.options.min_rows);
        
        this.baseX = (jQuery(window).width() - aw) / 2;
        this.baseY = this.jQuerywrapper.offset().top;
        
        if (this.options.autogenerate_stylesheet) {
            this.generate_stylesheet();
        }
        
        return this.generate_faux_grid(this.rows, this.cols);
    };
    
    /**
     * Destroy this gridster by removing any sign of its presence, making it easy to avoid memory leaks
     *
     * @method destroy
     * @param  {Boolean} remove If true, remove gridster from DOM.
     * @return {Object} Returns the instance of the Gridster class.
     */
    fn.destroy = function (remove) {
        this.jQueryel.removeData('gridster');
        
        // remove bound callback on window resize
        jQuery(window).unbind('.gridster');
        
        if (this.drag_api) {
            this.drag_api.destroy();
        }
        
        this.remove_style_tags();
        
        remove && this.jQueryel.remove();
        
        return this;
    };
    
    //jQuery adapter
    jQuery.fn.gridster = function (options) {
        return this.each(
            function () {
                if (!jQuery(this).data('gridster')) {
                    jQuery(this).data('gridster', new Gridster(this, options));
                }
            }
        );
    };
    
    return Gridster;
    
}));