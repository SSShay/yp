//菜单编辑插件
;(function(w,$){
    w.MenuEdit = function (opt) {
        opt = $.extend({
            'data': [],
            'menu_click': '',
            'item_click': '',
            'menu_add_click': '',
            'item_add_click': '',
            'sort_change':'',
        }, opt);

        var mousedown = 'mousedown';

        var t = {
            'create_menu': function(data){
                var $li = create_menu(data);
                menu_click($li);
                opt.menu_click &&　opt.menu_click($li);
                delete data.topid;
                opt.data.push(data);
            },
            'create_item': function(data){
                var $li = create_item(data,item_add);
                item_click($li);
                opt.item_click &&　opt.item_click($li);
                $.each(opt.data,function(i,v) {
                    if (v && v.id == data.topid) {
                        if (!opt.data[i].list) opt.data[i].list = [];
                        delete data.topid;
                        opt.data[i].list.push(data);
                    }
                })
            },
            'delete_menu':function(){
                var mid = select_menu.data('id');
                var iid,next;

                if(select_item) {
                    iid = select_item.data('id');
                    next = select_item.next();
                    select_item.remove();
                    if (next.hasClass('add-btn')) next.mousedown();
                    else {
                        item_click(next);
                        opt.item_click && opt.item_click(next);
                    }
                }
                else {
                    next = select_menu.next()
                    select_menu.remove();
                    if(next.hasClass('add-btn')) next.mousedown();
                    else{
                        menu_click(next);
                        opt.menu_click &&　opt.menu_click(next);
                    }
                }

                $.each(opt.data,function(i,v) {
                    if (v && v.id == mid) {
                        if(iid){
                            $.each(opt.data[i].list,function(j,vv){
                                if (vv && vv.id == iid) {
                                    delete opt.data[i].list[j];
                                    return false;
                                }
                            })
                        }else delete opt.data[i];
                        return false;
                    }
                })
            },
            'get_change':function() {
                change = [];
                var list = $(".navbar-nav li").not('.add-btn'),menu;
                $.each(opt.data,function(i,v) {
                    if (v) {
                        $.each(list, function (j, obj) {
                            return check_diff(v, obj);
                        });

                        if (v.list) {
                            var child_list = $(".nav-stacked li", menu);
                            $.each(v.list, function (j, vv) {
                                if(vv){
                                    $.each(child_list, function (k, item) {
                                        return check_diff(vv, item);;
                                    });
                                }
                            })
                        }
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
            }
        };

        var change;

        function check_diff(v,obj) {
            obj = $(obj);
            if (v.id == obj.data('id')) {
                var diff = {};
                var link = obj.data('url');
                if (v.link != link)diff.link = link;
                var sitemap = obj.data('sitemap');
                if (v.sitemap != sitemap)diff.sitemap = sitemap;
                var name = obj.children('a').text();
                if (v.name != name)diff.name = name;
                var sort = obj.index() + 1;
                if (v.sort != sort)diff.sort = sort;
                if (!$.isEmptyObject(diff)) {
                    diff.id = v.id;
                    change.push(diff);
                }
                return false;
            }
        }

        var menu_add = $('.nav-editor .navbar-nav>.add-btn').on(mousedown, function () {
            menu_click(menu_add)
            opt.menu_add_click && opt.menu_add_click(menu_add)
        }),item_add;

        function create_menu(f) {
            var $li = $([
                '<li data-url="', f.link, '" data-id="', f.id, '" data-sitemap="', f.sitemap, '">', '<a>', f.name, '</a>',
                '<ul class="nav nav-pills nav-stacked">',
                '<li class="add-btn item"><a><i class="fa fa-plus"></i></a></li>',
                '</ul>',
                '</li>'
            ].join(''));
            menu_add.before($li)
            var $a = $li.children('a').on(mousedown, function (e) {
                menu_click($li)
                drag_start(e, $li, opt.menu_click)
            })
            var itemadd = $('.add-btn', $li).on(mousedown, function () {
                item_click(itemadd);
                opt.item_add_click && opt.item_add_click(itemadd);
                item_add = itemadd;
            })

            return $li;
        }

        function create_item(e, item_add) {
            var $li = $([
                '<li data-url="', e.link, '" data-id="', e.id, '" data-sitemap="', e.sitemap, '">',
                '<a>', e.name, '</a>',
                '</li>'
            ].join(''));
            item_add.before($li);
            var $a = $li.children('a').on(mousedown, function (e) {
                item_click($li)
                drag_start(e, $li, opt.item_click)
            })
            return $li;
        }

        $(".nav-editor").on('dragstart', function () {
            return false;
        });

        var select_menu, select_item, drag;

        function menu_click($t) {
            select_menu && select_menu.removeClass('nav-active');
            select_menu = $t.addClass('nav-active');
            select_item && select_item.removeClass('active');
            select_item = null;
        }

        function item_click($tt) {
            select_item && select_item.removeClass('active');
            $(this).addClass('active')
            select_item = $tt.addClass('active');
        }

        function drag_start(e, $t, mu) {
            var css = $t.position();
            css.width = $t.width();
            drag = {
                'x': e.pageX - css.left, 'y': e.pageY - css.top,
                'w': css.width, 'h': $t.height(), 'i': $t.index(),
                'obj': $t.clone().addClass('drag-item').css(css),
                'place': $t.addClass('pos-item')
            };
            $t.parent().append(drag.obj);
            drag.obj.on('mouseup', function () {
                if (drag) {
                    mu && mu($t)
                    drag.place.removeClass('pos-item');
                    drag.obj.remove();
                    drag = null;
                }
            })
        }

        $('body').on('mousemove', function (e) {
            if (drag) {
                animate(function(){
                    if(drag){
                        drag.obj.css({'left': e.pageX - drag.x, 'top': e.pageY - drag.y})
                        var _pos = drag.obj.position();
                        drag.obj.siblings().not('.add-btn').each(function (i) {
                            var obj = $(this);
                            if (obj.hasClass('pos-item')) return true;
                            var pos = obj.position();
                            if (i != drag.i && is_contain(pos, drag.w, drag.h, _pos)) {
                                if (i < drag.i) obj.before(drag.place);
                                else obj.after(drag.place);
                                opt.sort_change && opt.sort_change();
                                drag.i = i;
                                return false;
                            }
                        });
                    }
                })
            }
        });

        function is_contain(p, w, h, _p) {
            return _p.left > p.left && _p.left < p.left + w && _p.top > p.top && _p.top < p.top + h
        }

        function init() {
            $.each(opt.data, function (i, f) {
                if(f){
                    var $li = create_menu(f);
                    if (f.list) {
                        $.each(f.list, function (j, e) {
                            e && create_item(e, $('.add-btn', $li))
                        })
                    }
                }
            })
        }

        init();
        return t;
    }
})(window,jQuery)

