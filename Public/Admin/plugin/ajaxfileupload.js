$.extend({
    createUploadIframe: function (id, uri) {
        //create frame
        var frameId = 'jUploadFrame' + id;
        var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';
        if (window.ActiveXObject) {
            if (typeof uri == 'boolean') {
                iframeHtml += ' src="' + 'javascript:false' + '"';
            }
            else if (typeof uri == 'string') {
                iframeHtml += ' src="' + uri + '"';
            }
        }
        iframeHtml += ' />';
        $(iframeHtml).appendTo(document.body);

        return $('#' + frameId).get(0);
    },
    createUploadForm: function (id, fileElement, data) {
        //create form
        var formId = 'jUploadForm' + id;
        var fileId = 'jUploadFile' + id;
        var form = $('<form action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');
        if (data) {
            for (var i in data) {
                if (data[i].name != null && data[i].value != null) {
                    $('<input type="hidden" name="' + data[i].name + '" value="' + data[i].value + '" />').appendTo(form);
                } else {
                    $('<input type="hidden" name="' + i + '" value="' + data[i] + '" />').appendTo(form);
                }
            }
        }

        var oldElement = $(fileElement);
        var newElement = oldElement.clone(true);
        oldElement.attr('id', fileId);
        oldElement.after(newElement);
        oldElement.appendTo(form);

        //set attributes
        form.css({'position': 'absolute', 'top': '-1200px', 'left': '-1200px'}).appendTo('body');
        return form;
    },

    ajaxFileUpload: function (s) {
        // TODO introduce global settings, allowing the client to modify them for all requests, not only timeout
        s = $.extend({}, $.ajaxSettings, s);
        var id = new Date().getTime()
        var form = $.createUploadForm(id, s.fileElement, (typeof (s.data) == 'undefined' ? false : s.data));
        var io = $.createUploadIframe(id, s.secureuri);
        var frameId = 'jUploadFrame' + id;
        var formId = 'jUploadForm' + id;
        // Watch for a new set of requests
        if (s.global && !$.active++) {
            $.event.trigger("ajaxStart");
        }
        var requestDone = false;
        // Create the request object
        var xml = {}
        if (s.global)
            $.event.trigger("ajaxSend", [xml, s]);
        // Wait for a response to come back
        var uploadCallback = function (isTimeout) {
            var io = document.getElementById(frameId);
            try {
                if (io.contentWindow) {
                    xml.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
                    xml.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;

                } else if (io.contentDocument) {
                    xml.responseText = io.contentDocument.document.body ? io.contentDocument.document.body.innerHTML : null;
                    xml.responseXML = io.contentDocument.document.XMLDocument ? io.contentDocument.document.XMLDocument : io.contentDocument.document;
                }
            } catch (e) {
                $.handleError(s, xml, null, e);
            }
            if (xml || isTimeout == "timeout") {
                requestDone = true;
                var status;
                try {
                    status = isTimeout != "timeout" ? "success" : "error";
                    // Make sure that the request was successful or notmodified
                    if (status != "error") {
                        // process the data (runs the xml through httpData regardless of callback)
                        var data = $.uploadHttpData(xml, s.dataType);
                        // If a local callback was specified, fire it and pass it the data
                        if (s.success)
                            s.success(data, status);

                        // Fire the global callback
                        if (s.global)
                            $.event.trigger("ajaxSuccess", [xml, s]);
                    } else
                        $.handleError(s, xml, status);
                } catch (e) {
                    status = "error";
                    $.handleError(s, xml, status, e);
                }

                // The request was completed
                if (s.global)
                    $.event.trigger("ajaxComplete", [xml, s]);

                // Handle the global AJAX counter
                if (s.global && ! --$.active)
                    $.event.trigger("ajaxStop");

                // Process result
                if (s.complete)
                    s.complete(xml, status);

                $(io).unbind()

                setTimeout(function () {
                    try {
                        $(io).remove();
                        form.remove();

                    } catch (e) {
                        $.handleError(s, xml, null, e);
                    }

                }, 100)

                xml = null

            }
        }
        // Timeout checker
        if (s.timeout > 0) {
            setTimeout(function () {
                // Check to see if the request is still happening
                if (!requestDone) uploadCallback("timeout");
            }, s.timeout);
        }
        try {

            var form = $('#' + formId);
            form.attr('action', s.url).attr('method', 'POST').attr('target', frameId);
            if (form[0].encoding) {
                form.attr('encoding', 'multipart/form-data');
            }
            else {
                form.attr('enctype', 'multipart/form-data');
            }
            form[0].submit();
        } catch (e) {
            $.handleError(s, xml, null, e);
        }

        $('#' + frameId).load(uploadCallback);
        return { abort: function () { } };

    },

    uploadHttpData: function (r, type) {
        var data = !type;
        if (!type)
            data = r.responseText;
        if (type == "xml")
            data = r.responseXML;
        //data = type == "xml" || data ? r.responseXML : r.responseText;
        // If the type is "script", eval it in global context
        if (type == "script")
            $.globalEval(data);
        // Get the JavaScript object, if JSON is used.
        if (type == "json") {
            ////////////Fix the issue that it always throws the error "unexpected token '<'"///////////////
            data = r.responseText;
            var start = data.indexOf(">");
            if (start != -1) {
                var end = data.indexOf("<", start + 1);
                if (end != -1) {
                    data = data.substring(start + 1, end);
                }
            }
            ///////////////////////////////////////////////////////////////////////////////////////////////
            eval("data = " + data);
        }
        // evaluate scripts within html
        if (type == "html")
            $("<div>").html(data).evalScripts();

        return data;
    },

    handleError: function (s, xhr, status, e) {
        // If a local callback was specified, fire it
        if (s.error) {
            s.error.call(s.context || s, xhr, status, e);
        }

        // Fire the global callback
        if (s.global) {
            (s.context ? $(s.context) : $.event).trigger("ajaxError", [xhr, s, e]);
        }
    }
})

$.fn.ajaxFileUpload = function(data,success,error) {
    if(success) data = {'data': data, 'success': success, 'error': error};
    var opt = $.extend({
        'url': '',
        'name': 'img',
        'data': '',
        'success': '',
        'error': function (data, status, e) {
            alert(e);
        }
    }, data);
    return this.each(function () {
        var $t = $(this);
        var finput = $('<input class="fileToUpload hidden" type="file" name="' + ($t.data('name') || opt.name) + '">');
        finput.change(function () {
            var data = opt.data ? opt.data.call($t) : {};
            $.ajaxFileUpload({
                url: $t.data('url') || opt.url, //用于文件上传的服务器端请求地址
                fileElement: $t.next(),
                data: data,
                dataType: 'json', //返回值类型 一般设置为json
                success: opt.success,
                error: opt.error,
            });
        })
        $t.after(finput).click(function () {
            $t.next().click()
        });
    });
}