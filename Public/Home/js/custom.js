;(function(w,$){
    //默认消息提醒配置
    var default_popover = {
        'container': 'body',
        'delay': {"show": 500, "hide": 0},
        'trigger':'manual',
        'html': 'true'
    };

    $.fixed_leave_msg = function() {
        var ismin = false;
        var t = {
            'min': function () {
                if (!ismin) {
                    $("#msg-board").css('left', '-100%');
                    setTimeout(function () {
                        $("#msg-board").addClass('min').css('left', 0);
                        ismin = true;
                    }, 400);
                }
            },
            'max': function () {
                if (ismin) {
                    $("#msg-board").css('left', '-100%');
                    setTimeout(function () {
                        $("#msg-board").removeClass('min').css('left', 0)
                        ismin = false;
                    }, 400);
                }
            },
            'ismin': function () {
                return ismin;
            },
            'isclick': function () {
                return isclick;
            }
        }
        var isclick = false;
        $("#msg-board .glyphicon").click(function () {
            t.min();
            isclick = true;
        })
        $("#msg-board img").click(function () {
            t.max();
        })

        return t;
    }

    //贝塞尔曲线动画
    $.cubic_bezier = function(p1x, p1y, p2x, p2y) {
        var cx = 3.0 * p1x;
        var bx = 3.0 * (p2x - p1x) - cx;
        var ax = 1.0 - cx - bx;

        var cy = 3.0 * p1y;
        var by = 3.0 * (p2y - p1y) - cy;
        var ay = 1.0 - cy - by;


        function sampleCurveX(t)
        {
            // `ax t^3 + bx t^2 + cx t' expanded using Horner's rule.
            return ((ax * t + bx) * t + cx) * t;
        }

        function sampleCurveDerivativeX(t)
        {
            return (3.0 * ax * t + 2.0 * bx) * t + cx;
        }

        function solveCurveX(x, epsilon) {
            if(epsilon == null) epsilon = 1;
            var t0, t1, t2, x2, d2, i;
            // First try a few iterations of Newton's method -- normally very fast.
            for (t2 = x, i = 0; i < 8; i++) {
                x2 = sampleCurveX(t2) - x;
                if (Math.abs(x2) < epsilon)
                    return t2;
                d2 = sampleCurveDerivativeX(t2);
                if (Math.abs(d2) < 1e-6)
                    break;
                t2 = t2 - x2 / d2;
            }
            // Fall back to the bisection method for reliability.
            t0 = 0.0;
            t1 = 1.0;
            t2 = x;
            if (t2 < t0)
                return t0;
            if (t2 > t1)
                return t1;
            while (t0 < t1) {
                x2 = sampleCurveX(t2);
                if (Math.abs(x2 - x) < epsilon)
                    return t2;
                if (x > x2)
                    t0 = t2;
                else
                    t1 = t2;
                t2 = (t1 - t0) * .5 + t0;
            }
            // Failure.
            return t2;
        }

        this.solve = function (x, epsilon) {
            var t = solveCurveX(x, epsilon);
            return ((ay * t + by) * t + cy ) * t
        }

        return this;
    }

    $.scrollTop = function(t,callback,b_arr) {
        $.scrollTo(0, t, callback, b_arr)
    }

    //页面滚动至
    $.scrollTo = function(top,t,callback,b_arr) {
        if (t == null) t = 1000;
        var _top = $(window).scrollTop();
        var d = top - _top;
        var timer;
        if (!b_arr) b_arr = [0.25, 0.1, 0.25, 1.0];//ease
        var bse = $.cubic_bezier(b_arr[0], b_arr[1], b_arr[2], b_arr[3]);
        var st = new Date().getTime(), ti, _topi = _top;

        function to() {
            ti = new Date().getTime() - st;
            if (ti < t) {
                var topi = _top + d * bse.solve(ti / t);
                if (Math.abs(topi - _topi) > 2) {
                    $(window).scrollTop(topi);
                    _topi = topi;
                }
                timer = animate(to);
            } else {
                cancelAnimate(timer);
                $(window).scrollTop(top);
                callback && callback()
            }
        }

        to();
    }

    //页面块滚动
    $.scrollBlock = function(opt) {
        "use strict"
        opt = $.extend({
            tolerance: 6,       //块滚动容差,防止滚动不精确造成的问题
            scrolltime: 700,     //块切换时间
            scrollcubic: null,  //块切换动画贝塞尔曲线
            blocklist: [],       //块的坐标集合
            wheelchange: 300,    //块内滚动每次变化
            wheeltime: 450,      //块内滚动每次时间
            wheelcubic: null,   //块内滚动动画贝塞尔曲线
            callback: function(prev,index) {

            }
        }, opt || {});
        var sb_list, scrolling,prev,index,N,lasth;

        window.scrollDisable(function (e) {
            var deltaY = e.deltaY || -e.wheelDelta;
            if (!scrolling) {
                var top = Math.round($(window).scrollTop());
                var H = $(window).height();
                if ((deltaY != 0)) {
                    var n_top, scroll;
                    var get_top = deltaY > 0 ? function (i, v) {
                        if (v > top + opt.tolerance) {
                            n_top = v;
                            index = i;
                            var _v = sb_list[i - 1];
                            if (_v != null) {
                                if (H < n_top - sb_list[i - 1] && top + H + opt.tolerance < n_top) {
                                    var tmp = top + opt.wheelchange;
                                    scroll = tmp + (opt.wheelchange >> 1) + H < n_top ? tmp : n_top - H;
                                }
                            }
                            return false;
                        } else if (i == N - 1 && lasth > H) {
                            scroll = top + opt.wheelchange;
                            return false;
                        }
                    } : function (i, v) {
                        if (v > top - opt.tolerance) {
                            index = i - 1;
                            var _v = sb_list[i - 1];
                            if (_v != null) {
                                n_top = _v == top ? sb_list[i - 2] : _v;
                            }
                            return false;
                        } else if (i == N - 1 && lasth > H) {
                            n_top = sb_list[i - 1] || v;
                            return false;
                        }
                    }
                    $.each(sb_list, get_top);

                    if (scroll) {
                        scrolling = true;
                        $.scrollTo(scroll, opt.wheeltime, enable, opt.wheelcubic);
                    }
                    else if (n_top != null && n_top != top) {
                        if (index != null) {
                            opt.callback && opt.callback(prev, index);
                            prev = index;
                        }
                        scrolling = true;
                        $.scrollTo(n_top, opt.scrolltime, enable, opt.scrollcubic);
                    }

                    index = null;
                }
            }
        });

        function reget() {
            sb_list = opt.blocklist;
            var i = 0;
            $(".scroll-block").each(function () {
                var $t = $(this);
                i = $t.data('i') || i;
                sb_list[i] = Math.round($t.position().top);
                i++;
            })
            lasth = $(".scroll-block").eq(-1).outerHeight();
            N = sb_list.length
        }

        function enable(){
            scrolling = false;
        }

        $(window).resize(reget)
        reget()
    }


    $.extend($, {

        //表单数据验证
        check: function (form, formtarget) {
            if (!$.isArray(form))form = [form];
            var data = {}
            $.each(form, function (i, v) {
                var $t = $(v.target, formtarget);
                $t.check(v, function (res) {
                    if (res === false) data = false;
                    else if (res != v['default']) {
                        data[v.key || v.target.replace(/[\. #]/g, "")] = res;
                    }
                })
                if (!data)return false;
            })

            return data;
        },
        info:function(s) {
            layer.alert(s, {icon: 1})
        },
        warn:function(s) {
            layer.alert(s, {icon: 0})
        },
        error:function(s) {
            layer.alert(s, {icon: 2})
        },
        regexs: {
            length: function (v, min, max) {
                if (min !== undefined && v.length < min) return false;
                if (max !== undefined && v.length > max) return false;
                return true;
            }
        }
    })

    $.rules = {
        empty: {
            error: '[NAME]不能为空',
            regex: function (v) {
                return v.length > 0;
            }
        },
        'mobile': {
            error: '请输入有效的手机号',
            regex: /^1[345678][0-9]{9}$/
        },
        'length': function (min, max) {
            var rules = {};
            if (max == undefined) rules.error = '[NAME]的长度应大于 ' + min + ' 个字符';
            else if (min == undefined)  rules.error = '[NAME]的长度应小于 ' + max + ' 个字符';
            else rules.error = '[NAME]的长度应在 ' + min + '~' + max + ' 个字符之间';

            rules.regex = function (v) {
                return $.regexs.length(v, min, max);
            }
            return rules;
        },
        number: function (min, max, fixed) {
            min = min >> 0;
            max = max >> 0;
            var rules = {error: '请输入有效的[NAME]'};
            rules.regex = function (v) {
                if (isNaN(v)) return false;
                if (v <= min) {
                    this.error = '请输入大于 ' + min + ' 的[NAME]';
                    return false;
                }
                if (v >= max) {
                    this.error = '请输入小于 ' + max + ' 的[NAME]';
                    return false;
                }
                if(fixed != null){
                    var arr = v.toString().split('.');
                    if (arr.length > 1 && arr[1].length > fixed) {
                        if(fixed == 0) this.error = '[NAME]应为整数';
                        else this.error = '[NAME]最多保留小数点后 ' + fixed + ' 位';
                        return false;
                    }
                }
                return $.regexs.length(v, min, max);
            };
            return rules;
        }
    }

    $.extend($.fn, {
        //错误提示
        wrong:function(error,placement,close_type) {
            return this.each(function () {
                var $t = $(this);
                $t.popover($.extend({}, default_popover, {
                    'content': '<span class="text-danger">' + error + '</span>',
                    'placement': placement || 'bottom'
                })).popover('show');
                if(close_type == -1 || close_type == undefined) {//input
                    $t.one('click', function () {
                        $t.popover('destroy')
                    });
                }else if(!close_type) {//button
                    $t.one('blur', function () {
                        $t.popover('destroy')
                    });
                }else {
                    setTimeout(function () {//timeout
                        $t.popover('destroy')
                    }, close_type * 1000)
                }
            });
        },

        //check
        check:function(opt,callback) {
            return this.each(function () {
                var $t = $(this), flag = true;
                opt = $.extend({
                    rules: $t.data('rules') || [],
                    name: $t.data('name'),//
                    value: $t.val(),
                    'default': $t.data('default'),
                    placement: $t.data('placement') || 'right'
                }, opt);

                if (!$.isArray(opt.rules)) {
                    opt.rules = [opt.rules];
                }
                $.each(opt.rules, function (i, r) {
                    r = $.extend({
                        error: '未知错误',
                        regex: ''
                    }, r);

                    if ($.isFunction(r.regex)) {
                        if (r.regex(opt.value))return true;
                    } else {
                        if (r.regex.test(opt.value))return true;
                    }

                    $t.wrong(r.error.replace(/\[NAME\]/g, opt.name), opt.placement);
                    return flag = false;
                })

                callback && callback(flag && opt.value);
            });
        },

        //按钮提交
        submit: function (url, data, callback, loadtxt, placement) {
            return this.each(function () {
                var $t = $(this);
                var txt = $t.text();
                $t.click(function () {
                    if ($t.is('.disabled')) return;
                    var _data;
                    if (data) {
                        if ($.isFunction(data)) {
                            _data = data();
                            if (!_data) return;
                        } else _data = data;
                    } else _data = null;

                    var txt = $t.text();
                    $t.addClass('disabled').text(loadtxt || '请稍等...');

                    $.ajax({
                        'url': url,
                        'type': 'post',
                        'data': _data,
                        'dataType': 'json',
                        'success': function (res) {
                            $t.removeClass('disabled').text(txt);
                            res.error && error(res.error)
                            callback && callback(res, _data);
                        },
                        'error': function () {
                            $t.removeClass('disabled').text(txt);
                            error('操作失败，请重试...')
                        }
                    })
                })

                function error(c) {
                    $t.wrong(c, placement);
                }

                return $t;
            })
        }
    })

    window.is_xs = function() {
        return $("button.navbar-toggle").css('display') != 'none';
    }
})(window,jQuery);

$(function() {

    //导航栏
    var nav_main = $(".navbar-main");
    var nav_btn = $("button.navbar-toggle");
    var nav_back = nav_main.children('.back');

    $(".navbar-nav>li", nav_main).each(function () {
        var $t = $(this);
        var list = $t.children('.item-list');
        if ($t.children('a').text() == panyard.indexnav) $t.addClass('active');
        $t.on({
            'mouseenter': function () {
                if (!is_xs()) {
                    nav_main.addClass('hover');
                    $t.siblings('.hover').removeClass('hover');
                    $t.addClass('hover')
                    var H = nav_main.height() + parseInt(nav_main.css('padding-top'));
                    var h = list.height();
                    if (h > 1) h += 40;
                    nav_back.height(H + h);
                }
            },
            'click': function () {
                if (is_xs()) {
                    $t.siblings('.action').children('.item-list').removeClass('action').slideUp(200);
                    list.slideDown(200, function () {
                        $t.addClass('action');
                    });
                }
            }
        })
        var list_items = list.children();
        if (list_items.length > 0) {
            var n = list_items.length >> 1;
            list.show()
            var item = list_items.eq(n);
            var l = item.position().left;
            if ((list_items.length & 1) == 0) l -= 50;
            else l += item.width() / 2 - 17;
            list.css({'display': '', 'left': $t.position().left - l});
        }
    })

    nav_main.on({
        'mouseleave': function () {
            if (nav_btn.css('display') == 'none') {
                nav_main.removeClass('hover')
                nav_back.height(0);
                $(".hover", nav_main).removeClass('hover')
            }
        }
    })

    $("#search-btn").click(function () {
        var $t = $(this);
        if (!is_xs() && !$t.parent().hasClass('focus')) {
            $t.parent().addClass('focus');
            $t.prev().focus()
        } else {
            console.info('search');
        }
    }).prev().blur(function (e) {
        var $t = $(this);
        if ($t.next().css('font-size') != '0px') {
            $t.parent().removeClass('focus');
        }
    })

    //右侧导航栏
    var topbtn = $(".nav-right .top").click(function () {
        $.scrollTop();
    })

    if (topbtn.length) {
        var istopshow = false;
        $(window).scroll(function () {
            var top = $(window).scrollTop();
            if (istopshow) {
                if (top <= 200) {
                    istopshow = false;
                    topbtn.stop().animate({'opacity': 0}, function () {
                        $(this).css('visibility', 'hidden')
                    })
                }
            } else {
                if (top > 200) {
                    istopshow = true;
                    topbtn.stop().css('visibility', 'visible').animate({'opacity': 1})
                }
            }
        })
    }

    $(".nav-right .contact_online").click(function () {
        var opts = {
            type: 2,
            title: '在线客服',
            content: panyard.u_contact_online,
            anim: 2,
            scrollbar: false
        }
        if(panyard.is_mobile){
            opts.area = ['100%', '0'];
            var index = layer.open(opts);
            layer.full(index);
        }else{
            opts.offset = 'rb';
            opts.area = ['320px', '480px'];
            layer.open(opts);
        }
    })

    $(".lazy").lazyload({effect: "fadeIn"});

    $(".container-leave-msg .btn").submit(panyard.u_leave_msg, function () {
        $("#name,#mobile,#msg").data('placement', is_xs() ? 'bottom' : 'right');
        return $.check([
            {'target': '#name', 'rules': $.rules.empty},
            {'target': '#mobile', 'rules': [$.rules.empty, $.rules.mobile]},
            {'target': '#msg', 'rules': $.rules.length(null, 200)},
        ])
    }, function (res) {
        if (res.success) {
            $("#name,#mobile,#msg").val('');
            $.info('留言成功！');
        }
    })

    if ($("#msg-board").length) {
        var fixed_leave_msg = $.fixed_leave_msg();

        if (panyard.is_mobile) {
            $("#msg-board .btn").click(function () {
                var index = layer.open({
                    type: 2,
                    title: '留言',
                    content: panyard.u_leave_msg,
                    area: ['100%', '0'],
                    scrollbar: false,
                    anim: 2
                });
                layer.full(index);
                window.close_leave_msg_box = function () {
                    layer.close(index)
                }
            })
        } else {
            $("#msg-board .btn").submit(panyard.u_leave_msg, function () {
                $("#msg-board .name,#msg-board .mobile,#msg-board .msg").data('placement', is_xs() ? 'bottom' : 'right');
                return $.check([
                    {'target': '.name', 'rules': $.rules.empty},
                    {'target': '.mobile', 'rules': [$.rules.empty, $.rules.mobile]},
                    {'target': '.msg', 'rules': $.rules.length(null, 200)},
                ])
            }, function (res) {
                if (res.success) {
                    $("#msg-board .name,#msg-board .mobile,#msg-board .msg").val('');
                    $.info('留言成功！');
                    fixed_leave_msg.min();
                }
            })
        }

        $(window).scroll(function (e) {
            var top = $(window).scrollTop();
            var pos = $(".container-leave-msg").position()
            if (pos) {
                var h = $(window).height()
                if (top > pos.top - h) {
                    fixed_leave_msg.min()
                } else {
                    if (!fixed_leave_msg.isclick())fixed_leave_msg.max()
                }
            }
        })
    }

})

//scroll禁用 支持
;(function () {
    var keys = {37: 1, 38: 1, 39: 1, 40: 1};
    var afterScroll;

    function preventDefault(e) {
        e = e || window.event;
        afterScroll && afterScroll(e)
        if (e.preventDefault)
            e.preventDefault();
        e.returnValue = false;
    }

    function preventDefaultForScrollKeys(e) {

    }

    var oldonwheel, oldonmousewheel1, oldonmousewheel2, oldontouchmove, oldonkeydown, isDisabled;

    window.scrollDisable = function (fn) {
        afterScroll = fn;
        if (window.addEventListener) // older FF
            window.addEventListener('DOMMouseScroll', preventDefault, false);
        oldonwheel = window.onwheel;
        window.onwheel = preventDefault; // modern standard
        oldonmousewheel1 = window.onmousewheel;
        window.onmousewheel = preventDefault; // older browsers, IE
        oldonmousewheel2 = document.onmousewheel;
        document.onmousewheel = preventDefault; // older browsers, IE
        /*oldontouchmove = window.ontouchmove;
        window.ontouchmove = preventDefault; // mobile
        oldonkeydown = document.onkeydown;
        document.onkeydown = preventDefaultForScrollKeys;*/

        isDisabled = true;
    };
    window.scrollEnable = function () {
        if (!isDisabled) return;
        if (window.removeEventListener)
            window.removeEventListener('DOMMouseScroll', preventDefault, false);

        window.onwheel = oldonwheel; // modern standard
        window.onmousewheel = oldonmousewheel1; // older browsers, IE
        document.onmousewheel = oldonmousewheel2; // older browsers, IE
        /*window.ontouchmove = oldontouchmove; // mobile
        document.onkeydown = oldonkeydown;*/

        isDisabled = false;
    };
    window.isScrollDisabled = function () {
        return isDisabled;
    }
})();

//animate 支持
;(function() {
    var lastTime = 0;
    var prefixes = 'webkit moz ms o'.split(' '); //各浏览器前缀

    var requestAnimationFrame = window.requestAnimationFrame;
    var cancelAnimationFrame = window.cancelAnimationFrame;

    var prefix;
    for (var i = 0; i < prefixes.length; i++) {
        if (requestAnimationFrame && cancelAnimationFrame) {
            break;
        }
        prefix = prefixes[i];
        requestAnimationFrame = requestAnimationFrame || window[prefix + 'RequestAnimationFrame'];
        cancelAnimationFrame = cancelAnimationFrame || window[prefix + 'CancelAnimationFrame'] || window[prefix + 'CancelRequestAnimationFrame'];
    }
    if (!requestAnimationFrame || !cancelAnimationFrame) {
        requestAnimationFrame = function (callback, element) {
            var currTime = new Date().getTime();
            var timeToCall = Math.max(0, 16 - ( currTime - lastTime ));
            var id = window.setTimeout(function () {
                callback(currTime + timeToCall);
            }, timeToCall);
            lastTime = currTime + timeToCall;
            return id;
        };

        cancelAnimationFrame = function (id) {
            window.clearTimeout(id);
        };
    }

    window.animate = requestAnimationFrame;
    window.cancelAnimate = cancelAnimationFrame;
})();