//菜单编辑插件
;(function(w,$) {
    w.ArticleEdit = function (editor, img_upload_url, img_del_url) {
        var opt = {
            'editor': editor,
            'img_upload_url': img_upload_url,
            'img_del_url': img_del_url,
        };
        var box = $(opt.editor).addClass('article-edit'), bar, modal, index;

        function set_bar(obj) {
            if (index != obj) {
                index && index.removeClass('active');
                index = obj.addClass('active');

                var p = index.position();
                var h = bar.outerHeight();
                var list = $('.btn', bar).removeClass('active');
                var header, align;
                var map = {'H1': 0, 'H2': 1, 'H3': 2, 'text-center': 5, 'text-right': 6};
                $.each(index.attr('class').split(' '), function (i, c) {
                    var v = map[c];
                    if (v != undefined) {
                        if (v < 4)header = true; else align = true;
                        list.eq(v).addClass('active');
                    }
                })
                !header && list.eq(3).addClass('active');
                !align && list.eq(4).addClass('active');
                bar.css({'left': 10, 'top': p.top - h});

                if (index.hasClass('img-block')) {
                    $(".header", bar).addClass('hidden');
                    $(".edit", bar).removeClass('hidden');
                } else {
                    $(".edit", bar).addClass('hidden');
                    $(".header", bar).removeClass('hidden');
                }
            }
        }

        function create_txt(before, v) {
            var txt = $('<textarea class="form-control"></textarea>').focus(function () {
                set_bar(txt)
            });
            if (!index) box.append(txt);
            else if (before) index.before(txt);
            else index.after(txt);
            if (v) txt.val(v);

            txt.on('paste', function (e) {
                var str = window.clipboardData && window.clipboardData.getData ?
                    window.clipboardData.getData('text/html') :
                    e.originalEvent.clipboardData.getData('text/html');
                e.preventDefault();
                t.html(str)
            })[0].focus();
            return txt;
        }

        function create_img(before, src, str) {
            var img = $('<div class="form-control text-center img-block"></div>').click(function(){
                set_bar(img)
            });
            $('<img src="' + (src ? src : ' ') + '"/>').error(function(){
                img.addClass('error');
            }).appendTo(img)
            str && img.append('<div>' + str + '<div>')
            if (!index) box.append(img);
            else if (before != 0) index.before(img);
            else index.after(img);
            img[0].click()

            return img;
        }

        function init() {
            modal = $([
                '<div class="modal fade in" role="dialog" aria-hidden="false">',
                '<div class="modal-dialog">',
                '<div class="modal-content">',
                '<div class="modal-header">',
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>',
                '<h5 class="modal-title">插入图片</h5>',
                '</div>',
                '<div class="modal-body row">',
                '<div class="col-md-5 img-modal">',
                '<img class="img-upload" data-url=' + opt.img_upload_url + '>',
                '<p class="mtop10">点击预览框<code>添加/更换</code>图片<br>建议格式：<code>*.jpg</code><br>建议大小：小于<code>256K</code></p>',
                '</div>',
                '<div class="col-md-7">',
                '<div class="form-group flat-blue single-row icheck">',
                '<label>插入位置：</label>',
                '<label class="radio"><input value="1" type="radio"  name="insert">之前</label>',
                '<label class="radio"><input value="0" type="radio"  name="insert" checked>之后</label>',
                '</div>',
                '<div class="form-group">',
                '<label>图片描述：</label>',
                '<textarea rows="2" class="form-control"></textarea>',
                '</div>',
                '</div>',
                '<button class="btn btn-primary mtop10 insert-img" type="button"></button>',
                '</div>',
                '</div>',
                '</div>',
                '</div>',
            ].join('')).appendTo(box);

            $(".flat-blue input", modal).iCheck({
                checkboxClass: 'icheckbox_flat-blue',
                radioClass: 'iradio_flat-blue'
            });

            var img_upload = $('.img-upload', modal).ajaxFileUpload(function () {
                var img = img_upload.attr('src');
                if (img) return {'img': img};
            }, function (data, status) {
                if (data.img) {
                    img_upload.attr('src', data.img);
                } else {
                    if (!data.error) data.error = '未知错误';
                    $.error(data.error);
                }
            })
            var img_txt = $('textarea', modal);

            var img_btn = $(".insert-img", modal).click(function () {
                var src = img_upload.attr('src');
                var txt = img_txt.val();
                if (img_btn.text() == '保存') {
                    $('img', index).attr('src', src);
                    index.removeClass('error');
                    var span = $('div', index).text(txt);
                    if (!span.length) index.append('<div>' + txt + '<div>');
                } else {
                    var insert = $(".radio input:checked", modal).val();
                    create_img(insert, src, txt);
                }
                modal.modal('hide')
                img_upload.removeAttr('src');
                img_txt.empty()
            })

            bar = $([
                '<div class="edit-bar">',

                '<div class="btn-group header">',
                '<button type="button" class="btn btn-default" data-class="H1" title="一级标题"><b>H1</b></button>',
                '<button type="button" class="btn btn-default" data-class="H2"  title="二级标题"><b>H2</b></button>',
                '<button type="button" class="btn btn-default" data-class="H3"  title="三级标题"><b>H3</b></button>',
                '<button type="button" class="btn btn-default" data-class=""  title="正文"><b>正文</b></button>',
                '</div>',

                '<div class="btn-group align">',
                '<button type="button" class="btn btn-default" data-class="" title="左对齐"><span class="fa fa-align-left"></span></button>',
                '<button type="button" class="btn btn-default" data-class="text-center" title="中间对齐"><span class="fa fa-align-center"></span></button>',
                '<button type="button" class="btn btn-default" data-class="text-right" title="右对齐"><span class="fa fa-align-right"></span></button>',
                '</div>',

                '<div class="btn-group">',
                '<button type="button" class="btn btn-default add-txt" title="插入文本"><span class="fa fa fa-plus"></span></button>',
                '<button type="button" class="btn btn-default add-img" title="插入图片"><span class="fa fa-picture-o"></span></button>',
                '</div>',

                '<div class="btn-group">',
                '<button type="button" class="btn btn-default edit" title="编辑"><span class="fa fa-edit"></span></button>',
                '<button type="button" class="btn btn-default remove" title="移除"><span class="fa fa-trash-o"></span></button>',
                '<button type="button" class="btn btn-default move" title="移动位置"><span class="fa fa-arrows"></span></button>',
                '</div>',

                '</div>',

            ].join(''))
            box.append(bar);
            $(".header>button,.align>button").each(function () {
                var $t = $(this);
                var c = $t.data('class');
                var others = $t.siblings();
                var cs = $.map(others, function (v) {
                    var c = $(v).data('class');
                    if (c) return c;
                }).join(' ');
                $t.click(function () {
                    $t.addClass('active')
                    others.removeClass('active');
                    index && index.removeClass(cs).addClass(c)[0].focus();
                })
            })

            $('.add-txt', bar).click(function () {
                create_txt();
            })
            $('.add-img', bar).click(function () {
                $('.icheck', modal).removeClass('hidden');
                img_btn.text('插入图片');
                modal.modal('show');
            })
            $('.edit', bar).click(function () {
                $('.icheck', modal).addClass('hidden');
                img_btn.text('保存');
                img_upload.attr('src', $('img', index).attr('src'));
                img_txt.val(index.text());
                modal.modal('show');
            })
            $('.remove', bar).click(function () {
                if (index) {
                    if (index.hasClass('img-block')) {
                        var img = $('img', index).attr('src').trim();
                        if (img) $.post(opt.img_del_url, {'img': img})
                    }
                    var next = index.next('.form-control');
                    if (!next.length) next = index.prev('.form-control');
                    index.remove()
                    index = null;
                    if (!next.length) create_txt();
                    else set_bar(next)
                }
            })

            var drag;
            $('.move', bar).on({
                'mousedown': function (e) {
                    var css = bar.position();
                    var pos = index.position();
                    pos.width = index.outerWidth();
                    drag = {
                        'x': e.pageX - css.left, 'y': e.pageY - css.top,
                        'X': e.pageX - pos.left, 'Y': e.pageY - pos.top,
                        'i': index.index() - 2,
                        'obj': index.clone().addClass('draging').css(pos).appendTo(index.parent()),
                    }
                    index.addClass('place')
                },
                'mouseup': function (e) {
                    if (drag) {
                        var obj = index.removeClass('place');
                        index = null;
                        set_bar(obj)
                        drag.obj.remove();
                        drag = null;
                    }
                }
            })

            $('body').on('mousemove', function (e) {
                if (drag) {
                    animate(function () {
                        if (drag) {
                            bar.css({'left': e.pageX - drag.x, 'top': e.pageY - drag.y});
                            drag.obj.css({'left': e.pageX - drag.X, 'top': e.pageY - drag.Y});
                            var _pos = drag.obj.position();
                            var list = drag.obj.siblings('.form-control');
                            var $o = list.eq(drag.i);
                            if (is_contain($o.position(), $o.outerWidth(), $o.outerHeight(), _pos)) return;
                            if (!is_contain({left: 0, top: 0}, box.width(), box.height(), _pos)) return;
                            list.each(function (i) {
                                var obj = $(this);
                                if (obj.hasClass('draging')) return true;
                                var pos = obj.position();
                                if (i != drag.i && is_contain(pos, obj.width(), obj.height(), _pos)) {
                                    if (i < drag.i) obj.before(index);
                                    else obj.after(index);
                                    drag.i = i;
                                    opt.sort_change && opt.sort_change();
                                    return false;
                                }
                            });
                        }
                    })
                }
            });
            function is_contain(p, w, h, _p) {
                return _p.left > p.left && _p.left < p.left + w && _p.top > p.top && _p.top < p.top + h;
            }
        }

        init();

        var t = {
            'empty': function () {
                box.children('.form-control').remove();
                index = null;
            },
            'lead': function () {
                create_txt()
            },
            'select':function() {
                var fir = box.children('.form-control')[0];
                fir.click();
                fir.focus();
            },
            'html': function (s) {
                if (s != undefined) {
                    var html = $(s);
                    html.each(function (i, t) {
                        var $t = $(t), obj;
                        if ($t.is('style') || $t.is('script') || $t.is('meta')) return;
                        if ($t.hasClass('img-block')) {
                            obj = create_img(0, $('img', $t).attr('src'), $t.text())
                        } else {
                            if ($t.is('img')) {
                                obj = create_img(0, img.src)
                            } else {
                                var txt = $t.text().trim();
                                if (txt) {
                                    obj = create_txt(0, $t.text())
                                    obj.addClass($t.is('h1') ? 'H1' : $t.is('h2') ? 'H2' : $t.is('h3') ? 'H3' : '')
                                }
                            }
                            var imgs = $('img', $t);
                            imgs.each(function (i, img) {
                                var $img = create_img(0, img.src);
                                var align = $(img).parent().css('text-align');
                                if (align == 'center' || align == 'right') $img.addClass('text-' + align);
                            })
                        }

                        if (obj) {
                            var align = $t.css('text-align');
                            if (align == 'center' || align == 'right') obj.addClass('text-' + align)
                        }

                    })

                    var list = box.children('.form-control');
                    if (!list.length) create_txt();
                    else set_bar(list.eq(0));
                } else {
                    var html = [];
                    box.children('.form-control').each(function (i, t) {
                        var $t = $(t);
                        var ele = $t.hasClass('img-block') ? $('<div class="img-block">' + $t.html().replace(/\n/g, '<br>') + '</div>') : gettarget($t);
                        addclass($t, ele);
                        html.push(ele.prop("outerHTML"));
                    })
                    function gettarget(obj) {
                        var map = {'H1': 'h1', 'H2': 'h2', 'H3': 'h3'};
                        var ele;
                        $.each(map, function (k, v) {
                            if (obj.hasClass(k)) {
                                ele = $('<' + v + '>');
                                return false;
                            }
                        })
                        if (!ele) ele = $('<p>');
                        return ele.text(obj.val().replace(/\n/g, '<br>'));
                    }

                    function addclass(obj, ele) {
                        var map = ['text-center', 'text-right'];
                        $.each(map, function (k, v) {
                            if (obj.hasClass(v)) {
                                ele.addClass(v);
                                return false;
                            }
                        })
                    }

                    return html.join('\r\n')
                }
            },
            'text': function (l) {
                var text = '';
                box.children('.form-control').each(function (i, t) {
                    var $t = $(t);
                    if (!$t.hasClass('img-block')) text += $t.val().replace(/\n| /g, '')
                    if (l > 0 && text.length > l) return false
                })
                return text;
            }
        };

        return t;
    }
})(window,jQuery)