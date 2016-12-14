//自定义Zepto方法
;(function($) {

    var default_popover = {
        'container': 'body',
        'delay': {"show": 500, "hide": 0},
        'trigger': 'manual',
        'html': 'true',
    }

    $.extend($, {

        //表单数据验证
        check: function (form, formtarget) {
            if (!$.isArray(form))form = [form];
            var data = {}
            $.each(form, function (i, v) {
                /*v = $.extend({
                 target: '',
                 key:'',
                 rules: $t.data('rules') || [],
                 name: $t.data('name'),//
                 value: $t.val(),
                 default: $t.val(),
                 placement: 'right',
                 }, v);*/
                var $t = $(v.target, formtarget);

                $t.check(v, function (res) {
                    if (res === false) data = false;
                    if (res !== null){
                        data[v.key || v.target.replace(/[\. #]/g, "")] = res;
                    }
                })

                if (!data) return false;
            })

            return data;
        },
        info: function (s) {
            alert(s)
        },
        warn: function (s) {
            alert(s)
        },
        error: function (s) {
            alert(s)
        },
        confirm: function(s,fn) {
            confirm(s) && fn && fn();
        },
        regexs: {
            length: function (v, min, max) {
                if (min !== undefined && v.length < min) return false;
                if (max !== undefined && v.length > max) return false;
                return true;
            }
        }
    });

    $.rules = {
        empty: {
            error: '[NAME]不能为空',
            regex: function (v) {
                return v.length > 0;
            }
        },
        url: {
            error: '请输入有效的[NAME]',
            regex: /(https|http|ftp|rtsp|igmp|file|rtspt|rtspu):\/\/((((25[0-5]|2[0-4]\d|1?\d?\d)\.){3}(25[0-5]|2[0-4]\d|1?\d?\d))|([0-9a-z_!~*'()-]*\.?))([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.([a-z]{2,6})(:[0-9]{1,4})?([a-zA-Z/?_=]*)\.\w{1,5}/
        },
        'mobile': {
            error: '请输入有效的手机号',
            regex: /^1[345678][0-9]{9}$/,
        },
        length: function (min, max) {
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
                var arr = v.toString().split('.');
                if (arr.length > 1 && arr[1].length > fixed) {
                    this.error = '[NAME]最多保留小数点后 ' + fixed + ' 位';
                    return false;
                }
                return $.regexs.length(v, min, max);
            };
            return rules;
        }
    };

    $.extend($.fn, {
        focusend: function () {
            var $t = $(this);
            var t = $t[0];
            t.focus();
            var len = $t.val().length;
            if (document.selection) {
                var sel = t.createTextRange();
                sel.moveStart('character', len); //设置开头的位置
                sel.collapse();
                sel.select();
            } else if (typeof t.selectionStart == 'number' && typeof t.selectionEnd == 'number') {
                t.selectionStart = t.selectionEnd = len;
            }
        },
        //错误提示
        wrong: function (error, placement, close_type) {
            return this.each(function () {
                var $t = $(this);
                $t.popover($.extend({}, default_popover, {
                    'content': '<span class="text-danger">' + error + '</span>',
                    'placement': placement || 'bottom',
                })).popover('show');
                if (close_type == -1 || close_type == undefined) {//input：-1,null
                    $t.one('click', function () {
                        $t.popover('destroy')
                    });
                } else if (!close_type) {//button：0
                    $t.one('blur', function () {
                        $t.popover('destroy')
                    });
                } else {
                    setTimeout(function () {//timeout：>0
                        $t.popover('destroy')
                    }, close_type * 1000)
                }
            });
        },

        //check
        check: function (opt, callback) {
            return this.each(function () {
                var $t = $(this), flag = true;
                opt = $.extend({
                    rules: $t.data('rules') || [],
                    name: $t.data('name'),//
                    value: $t.val(),
                    'default': $t.data('default'),
                    placement: $t.data('placement') || 'right',
                }, opt);
                if(opt['default'] != opt.value){
                    if (!$.isArray(opt.rules)) {
                        opt.rules = [opt.rules];
                    }
                    $.each(opt.rules, function (i, r) {
                        r = $.extend({
                            error: '未知错误',
                            regex: '',
                        }, r);

                        if ($.isFunction(r.regex)) {
                            if (r.regex(opt.value))return true;
                        } else {
                            if (r.regex.test(opt.value))return true;
                        }

                        $t.wrong(r.error.replace(/\[NAME\]/g, opt.name), opt.placement, -1);
                        return flag = false;
                    })
                }else{
                    flag = null;
                }
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
                            callback && callback(res,_data);
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

})(jQuery)


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
})()