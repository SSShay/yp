//菜单编辑插件
;(function(w,$) {
    $.fn.PageNumber = function(index,maxcell,loadpage) {
        var pagebox = $(this).addClass('pagination');
        var page = $('<ul></ul>').appendTo(pagebox);
        var max, ellipsis;
        var t = {
            'index': null,
            'count': null,
            'refresh': function (i, n) {
                if (t.count != n || ellipsis) {
                    t.count = n;
                    max = maxcell(page, t.count);
                    create(i);
                }
                active(i);
            },
            'loadpage':loadpage,
            'loadindex':function(){
                t.loadpage(index);
            }
        }

        if ($.isFunction(maxcell)) {
            $(w).resize(function () {
                animate(function () {
                    var _max = maxcell(page, t.count);
                    if (max == _max || (max > t.count && _max > t.count)) return;
                    max = _max;
                    create(index);
                    active(index);
                })
            })
        } else {
            max = maxcell || 6;
        }

        function create(p) {
            page.html('');
            var start, end;
            $('<li><a>上一页</a></li>').appendTo(page).click(function () {
                if (t.index > 0)loadpage(t.index - 1);
            })
            if (max < t.count && max > 2) {
                max -= 2;
                start = p - ((max - 2) >> 1);
                end = start + max - 2;
                if (start < 3) {
                    start = 1;
                    end = max;
                } else if (end > t.count - 3) {
                    start = t.count - max;
                    end = t.count - 1;
                }
                ellipsis = true;
            } else {
                start = 1;
                end = t.count - 1;
                ellipsis = false;
            }

            cpage(0);
            if (start != 1) page.append('<li class="ellipsis"><a>...</a></li>')
            for (var i = start; i < end; i++) cpage(i);
            if (end != t.count - 1) page.append('<li class="ellipsis"><a>...</a></li>')
            if (t.count > 1) cpage(t.count - 1);
            $('<li><a>下一页</a></li>').appendTo(page).click(function () {
                if (t.index < t.count - 1) loadpage((t.index >> 0) + 1);
            })
            function cpage(i) {
                $('<li><a>' + (i + 1) + '</a></li>').appendTo(page).click(function () {
                    loadpage(i);
                })
            }

            t.index = null;
        }

        function active(i) {
            if (t.index != i) {
                var list = $("li", page).each(function () {
                    var $t = $(this), str = $t.text() - 1;
                    if (t.index == str) $t.removeClass('active');
                    if (i == str) $t.addClass('active');
                });

                if (i <= 0) list.eq(0).addClass('disabled')
                else list.eq(0).removeClass('disabled')
                if (i >= t.count - 1) list.eq(-1).addClass('disabled')
                else list.eq(-1).removeClass('disabled')
                t.index = i;
                index = i;
            }
        }

        return t;
    }
})(window,jQuery)