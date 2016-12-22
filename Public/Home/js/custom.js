;(function(w,$){

    var default_popover = {
        'container': 'body',
        'delay': {"show": 500, "hide": 0},
        'trigger':'manual',
        'html': 'true'
    };

    var scroll_d = 10;
    $.scrollTop = function(t){
        var top = $(window).scrollTop();
        var h = $("body").height() - $(window).height();
        if (top > 0) {
            if (t) $(window).scrollTop(top - scroll_d);
            else scroll_d = Math.ceil(top / 10);
            animate($.scrollTop)
        }
    }

    $.scrollTo = function(top,t,callback) {

        t = ((t || 1000) / 100) >> 0;
        var _top = $(window).scrollTop();
        var sd = (top - _top) / t;
        var flag = top > _top ? 1 : -1;
        var timer

        function to() {
            _top += sd;
            if ((top - _top) * flag > 0) {
                $(window).scrollTop(_top);
                timer = animate(to);
            }else{
                cancelAnimate(timer);
                $(window).scrollTop(top);
                callback && callback()
            }
        }

        to();
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
            alert(s)
        },
        warn:function(s){
            alert(s)
        },
        error:function(s) {
            alert(s)
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


})(window,jQuery);

$(function() {

    //导航栏
    var nav_main = $(".navbar-main");
    var nav_btn = $("button.navbar-toggle");
    var nav_back = nav_main.children('.back');

    window.is_xs = function() {
        return nav_btn.css('display') != 'none';
    }

    $(".navbar-nav>li", nav_main).each(function () {
        var $t = $(this);
        var list = $t.children('.item-list');
        if($t.children('a').text() == panyard.indexnav) $t.addClass('active');
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
    var topbtn = $(".nav-right .top").click(function(){
        $.scrollTop();
    })

    if(topbtn.length){
        var istopshow = false;
        $(window).scroll(function() {
            var top = $(window).scrollTop();
            if(istopshow){
                if (top <= 200) {
                    istopshow = false;
                    topbtn.stop().animate({'opacity': 0}, function () {
                        $(this).css('visibility', 'hidden')
                    })
                }
            }else{
                if (top > 200) {
                    istopshow = true;
                    topbtn.stop().css('visibility', 'visible').animate({'opacity': 1})
                }
            }
        })
    }

    //延迟加载插件
    $(".img-delay").each(function () {
        var $t = $(this);
        function show() {
            var bg = $t.data('bg');
            var img = $t.data('img');
            if (bg) loadimg(bg, function () {
                var back = $t.css('background');
                var backimg = "url('" + bg + "')";
                var imgtmp = $("<div class='imgtmp'></div>").prependTo($t);
                if (back) imgtmp.css('background', back);
                else if ($t.hasClass("bg-cover")) imgtmp.addClass("bg-cover");

                imgtmp.css('background-image', backimg).animate({'opacity': 1}, 1000, function () {
                    $t.css('background-image', backimg);
                    imgtmp.remove();
                })

            });
            if (img) loadimg(img, function () {
                $t.css('src', img);
            });
            function loadimg(src, callback) {
                var Img = new Image();
                Img.src = src;
                $(Img).bind('load', callback).bind('error', function () {
                    console.log('error')
                })
                if (Img.complete) setTimeout(callback, 300);
            }
        }

        if ($t.is(".trigger-show")) {
            $t.bind('trigger', function(){
                show();
                $t.removeClass("trigger-show")
            });
        }
        else show();
    })

    $(".container-leave-msg .btn").submit("{:U('Index/leave_msg')}", function () {
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
        if (keys[e.keyCode]) {
            preventDefault(e);
            return false;
        }
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
        oldontouchmove = window.ontouchmove;
        window.ontouchmove = preventDefault; // mobile
        oldonkeydown = document.onkeydown;
        document.onkeydown = preventDefaultForScrollKeys;

        isDisabled = true;
    };
    window.scrollEnable = function () {
        if (!isDisabled) return;
        if (window.removeEventListener)
            window.removeEventListener('DOMMouseScroll', preventDefault, false);

        window.onwheel = oldonwheel; // modern standard
        window.onmousewheel = oldonmousewheel1; // older browsers, IE
        document.onmousewheel = oldonmousewheel2; // older browsers, IE
        window.ontouchmove = oldontouchmove; // mobile
        document.onkeydown = oldonkeydown;

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