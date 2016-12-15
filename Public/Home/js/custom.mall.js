//自定义jQuery方法
;(function($) {

    //number-box插件
    $.fn.numberBox = function(opts) {
        return this.each(function () {
            var $t = $(this);
            var opt = $.extend({
                min: $t.data('min'),
                max: $t.data('max'),
                change: function (v) {
                    console.log(v);
                },
                error: function (errorTxt) {
                    console.log(errorTxt);
                }
            }, opts);

            var input = $("input", $t).on({
                'keydown': function (e) {
                    var code = e.keyCode || window.event.keyCode, key;
                    if (code == 189) key = '-';
                    if (code > 36 && code < 41) return; //方向键
                    if (code > 95 && code < 106) key = code - 96;
                    else key = String.fromCharCode(code);
                    var pos = getpos(input[0]);
                    var arr = input.val().split('');
                    if (e.keyCode == 8) {
                        key = '';
                        if (pos.start == pos.end) pos.start--;
                    }
                    arr.splice(pos.start, pos.end - pos.start, key)
                    var val = arr.join('');
                    if (isNaN(val)) {
                        var v = input.attr('readonly', true).val();
                        setTimeout(function () {
                            input.removeAttr('readonly').val(v);
                        }, 10);
                        return error("输入的值不是数字");
                    }
                    if (val < opt.min) {
                        setval(opt.min);
                        return error("输入的值不能小于 MIN");
                    }
                    if (val > opt.max) {
                        setval(opt.max);
                        return error("输入的值不能大于 MAX");
                    }
                    opt.change && opt.change(val)
                    function getpos(el) {
                        var pos = {};
                        el.focus();
                        if (el.selectionStart) {
                            pos.start = el.selectionStart;
                            pos.end = el.selectionEnd;
                        }
                        else if (document.selection) { // IE
                            var range, textRange, duplicate;
                            range = document.selection.createRange()
                            if (range == null) {
                                pos.start = el.value.length;
                                pos.end = pos.start;
                            }
                            else {
                                textRange = el.createTextRange();
                                pos.end = textRange.text.length;
                                duplicate = textRange.duplicate();
                                textRange.moveToBookmark(range.getBookmark())
                                duplicate.setEndPoint('EndToStart', textRange)
                                pos.start = duplicate.text.length;
                                pos.end += pos.start;
                            }
                        }
                        return pos;
                    }

                    function error(s) {
                        opt.error && opt.error(s);
                        e.stopPropagation();
                        e.preventDefault();
                        return false;
                    }
                }, 'paste': function () {
                    return false;
                }
            }).css('ime-mode', 'disabled');
            var btns = $(".input-group-addon", $t);
            btns.eq(0).click(function () {
                setval(Math.max(input.val() - 1, opt.min))
            })
            btns.eq(1).click(function () {
                setval(Math.min(parseFloat(input.val()) + 1, opt.max))
            })

            var v = input.val();
            if (v < opt.min) setval(opt.min);
            else if (v > opt.max) setval(opt.max);

            function setval(v) {
                var _v = input.val();
                if(_v != v){
                    input.val(v);
                    opt.change && opt.change(v)
                }
            }
        })
    }

})(jQuery)