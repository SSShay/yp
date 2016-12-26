//菜单编辑插件
;(function(w,$) {
    w.EditTable = function (opts) {
        opts = $.extend({
            'table': '',
            'save_url': '',
            'del_url': '',
            'rowkeys': [],
            'coledits':[],
            row_del: function ($tr, res) {
                $tr.remove();
            },
            cell_edit: function ($td, res) {

            }
        }, opts);

        var $t = $(opts.table).addClass('edit-table');
        if ($.isEmptyObject(opts.rowkeys)) {
            opts.rowkeys = [];
            $("th", $t).each(function () {
                opts.rowkeys.push($(this).data('key'));
            });
        }
        if (!opts.rowkeys) console.error('rowkeys 未配置！');

        if ($.isEmptyObject(opts.coledits)) {
            opts.coledits = [];
            $("th", $t).each(function (i) {
                var edit = $(this).data('edit');
                if (edit) {
                    if (edit == 'text') {
                        opts.coledits[i] = true;
                    } else {
                        opts.coledits[i] = $(this).data('data');
                    }
                }
            });
        }

        $("tbody tr", $t).each(function () {
            row_bind_event($(this))
        });

        var flag, input;
        function row_bind_event($tr) {
            var id = $tr.data('id');
            var key = $tr.data('key') || 'id';
            if (!id) console.error('id 未配置！');
            var data = {};
            data[key] = id;
            $("td", $tr).each(function (d) {
                var $td = $(this);
                if ($td.is('.action')) {
                    $(".del-btn", $td).click(function () {
                        $.confirm('确定要删除这条数据吗？', function () {
                            $.post(opts.del_url, data, function (res) {
                                if (res.success) {
                                    opts.row_del($tr, res)
                                } else {
                                    $.error(res.error || '删除失败！');
                                }
                            }, 'JSON')
                        })
                    })
                } else {
                    var opt = opts.coledits[d];
                    if(opt){
                        var txt;
                        if (opt === true) {
                            $td.css('cursor', 'text').click(function () {
                                if(!check()) return;
                                txt = $td.text();
                                input = $(['<div class="input-group">',
                                    '<input class="form-control" type="text" value="', txt, '">',
                                    '<div class="input-group-addon btn btn-primary"><i class="fa fa-check"></i></div>',
                                    '</div>'].join(''));
                                bind_save_event(function(){
                                    return $("input", input).val()
                                },function(v){
                                    txt = v;
                                    $td.text(v);
                                });
                                flag = id + '_' + d;
                                $td.append(input);
                                $("input", input).focusend();
                            })
                        }else{
                            $td.css('cursor', 'pointer').click(function () {
                                if(!check()) return;
                                txt == null && (txt = $td.data('val'));
                                input = $(['<div class="input-group">',
                                    '<select class="form-control">', $.map(opt, function (v, k) {
                                        return '<option value="' + k + '">' + v + '</option>'
                                    }).join(''), '</select>',
                                    '<div class="input-group-addon btn btn-primary"><i class="fa fa-check"></i></div>',
                                    '</div>'].join(''));
                                bind_save_event(function(){
                                    return $("select", input).val();
                                },function(v){
                                    txt = v;
                                    $td.text(opt[v]);
                                });
                                flag = id + '_' + d;
                                $("select", input).val(txt);
                                $td.append(input);
                            })
                        }

                        function save() {
                            setTimeout(function () {
                                input.remove()
                                input = null;
                                flag = null;
                                $td.removeClass('editing');
                            }, 1);
                        }

                        function check(){
                            if (flag) {
                                if (flag != id + '_' + d) input.wrong("请先保存当前编辑器的值！");
                                return;
                            }
                            if ($td.hasClass('editing')) return;
                            $td.addClass('editing');
                            return true
                        }

                        function bind_save_event(getval,setval){
                            $(".btn", input).click(function () {
                                var val = getval();
                                if (val == txt) save(val)
                                else {
                                    var _data = $.extend({}, data);
                                    _data[opts.rowkeys[d]] = val;
                                    $.post(opts.save_url, _data, function (res) {
                                        if (res.success) {
                                            opts.cell_edit($td, res, val);
                                            save(val);
                                            setval && setval(val);
                                        } else {
                                            input.wrong(res.error || "保存失败！");
                                        }
                                    }, 'JSON')
                                }
                            })
                        }
                    }
                }
            })
        }

        return {
            row_bind_event: function ($row) {
                row_bind_event($row)
            }
        }
    }
})(window,jQuery);