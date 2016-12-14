//菜单编辑插件
;(function(w,$){
    w.BoxDarg = function(opt){
        opt = $.extend({
            'data': [],
            'target':'.img-thumb-list',
            'box_click': '',
            'sort_change': '',
            'create_box': function (v) {
                return $([
                    '<div class="col-xs-6 col-sm-4 col-md-3">',
                    '<div class="img-thumb" data-id="' + v.id + '">',
                    '<img src="">',
                    '</div>',
                    '</div>',
                ].join(''));
            },
            'check_diff': function (obj, v) {

            }
        }, opt);

        var drag,select;

        var t = {
            'box_create': function (v) {
                opt.data.push(v)
                var box = create_box(v)
                return box;
            },
            'box_remove':function(obj) {
                obj && (select = obj);
                var id = select.data('id');
                var $p = select.parent();
                var next = $p.next();
                if (!next.length) next = $p.prev();
                $p.remove();

                $.each(opt.data, function (i, v) {
                    if (v && v.id == id) {
                        delete opt.data[i];
                        return false;
                    }
                })
                click_box(next.children(".img-thumb"))
            },
            'get_change':function() {
                var change = [];
                var list = $(".img-thumb", boxs);

                $.each(opt.data, function (i, v) {
                    if (v) {
                        $.each(list, function (j, obj) {
                            var res = opt.check_diff(j, $(obj), v);
                            if (res) {
                                if (!$.isEmptyObject(res)) {
                                    res.id = v.id;
                                    change.push(res);
                                }
                                return false;
                            }
                        });
                    }
                })

                return change;
            },
            'save_change':function(data) {
                $.each(opt.data, function (i, v) {
                    if (v) {
                        $.each(data, function (j, _v) {
                            if (v.id = _v.id) {
                                $.extend(opt.data[i], _v)
                            }
                        });
                        return false;
                    }
                })
            },
            'get_next_sort':function() {
                var max = 0;
                $.each(opt.data, function (i, v) {
                    if (v && max < v.sort) max = v.sort
                })
                return (max >> 0) + 1;
            },
            'get_count':function(){
                return $(".img-thumb",boxs).length
            }
        }

        var boxs = $(opt.target).on('dragstart', function () {
            return false;
        });

        $('body').on('mousemove', function (e) {
            if (drag) {
                animate(function () {
                    if (drag) {
                        drag.obj.css({'left': e.pageX - drag.x, 'top': e.pageY - drag.y})
                        var _pos = drag.obj.position();
                        var list = drag.obj.siblings();
                        var $o = list.eq(drag.i);
                        if (is_contain($o.position(), $o.outerWidth(), $o.outerHeight(), _pos)) return;
                        if (!is_contain({left: 0, top: 0}, boxs.width(), boxs.height(), _pos)) return;
                        list.each(function (i) {
                            var obj = $(this);
                            if (obj.hasClass('draging')) return true;
                            var pos = obj.position();
                            if (i != drag.i && is_contain(pos, drag.w, drag.h, _pos)) {
                                if (i < drag.i) obj.before(drag.place.parent());
                                else obj.after(drag.place.parent());
                                drag.i = i;
                                opt.sort_change && opt.sort_change();
                                return false;
                            }
                        });
                    }
                })
            }
        });

        function click_box(obj){
            select && select.removeClass('active')
            select = obj.addClass('active');
            opt.box_click && opt.box_click(select)
        }

        function create_box(v) {
            var box = opt.create_box(v);
            var thumb = box.children('.img-thumb')
            thumb.on({
                'mousedown': function (e) {
                    var $t = $(this);
                    var $p = $t.parent();
                    var css = $t.position();
                    var pos = $p.position();
                    css.width = $t.outerWidth();
                    css.left += pos.left;
                    css.top += pos.top;
                    drag = {
                        'x': e.pageX - css.left, 'y': e.pageY - css.top,
                        'w': css.width, 'h': $t.height(), 'i': $p.index(),
                        'obj': $t.clone(),
                        'place': $t.addClass('place')
                    };
                    $p.parent().append(drag.obj.addClass('draging').css(css));
                    drag.obj.on('mouseup', function () {
                        if (drag) {
                            drag.obj.remove();
                            click_box(drag.place.removeClass('place'));
                            drag = null;
                            console.log(count / ++n)
                        }
                    })
                }
            });
            click_box(thumb);
            boxs.append(box)
            return box;
        }
        var n = 0,count = 0;
        function is_contain(p, w, h, _p) {
            count++;
            return _p.left > p.left && _p.left < p.left + w && _p.top > p.top && _p.top < p.top + h;
        }

        function init() {
            $.each(opt.data, function (i, v) {
                if (v) create_box(v);
            })
        }

        init();
        return t;
    }
})(window,jQuery)