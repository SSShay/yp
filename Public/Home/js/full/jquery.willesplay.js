(function($,w,d) {
    $.fn.video = function () {
        var $t = $(this);
        $t.append($('<div class="playHeader"><div class="videoName"></div></div>'))
        var titleBar = $(".videoName", $t);
        var playContent = $([
            '<div class="playContent">',
            '<video width="100%" src="" height="100%">',
            '当前浏览器不支持 video直接播放，点击这里下载视频： <a href="/">下载视频</a>',
            '</video>',
            '<div class="playTip glyphicon glyphicon-play"></div>',
            '</div>'
        ].join(""));

        var playControl = $([
            '<div class="playControll">',
            '<div class="playPause playIcon"></div>',
            '<div class="timebar">',
            '<span class="currentTime">0:00:00</span>',
            '<div class="progress">',
            '<div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>',
            '</div>',
            '<span class="duration">0:00:00</span>',
            '</div>',
            '<div class="otherControl">',
            '<span class="volume glyphicon glyphicon-volume-down text-primary"></span>',
            '<span class="fullScreen glyphicon glyphicon-fullscreen"></span>',
            '<div class="volumeBar">',
            '<div class="volumewrap">',
            '<div class="progress">',
            '<div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 8px;height: 40%;"></div>',
            '</div>',
            '</div>',
            '</div>',
            '</div>',
            '</div>'
        ].join(""));

        $t.append(playContent, playControl);
        var playVideo = $('video', playContent);
        var playTip = $('.playTip', playContent);
        var playPause = $('.playPause', playControl); //播放和暂停
        var currentTime = $('.currentTime', playControl); //当前时间
        var duration = $('.duration', playControl); //总时间
        var volumebar = $('.volumeBar', playControl);
        var progress = $('.timebar .progress', playControl); //进度条
        var prbar = $('.progress-bar', progress); //进度条
        var volumewrap = $('.volumewrap', volumebar);
        var volumeprbar = $('.progress-bar', volumewrap);
        var volume = $('.volume', playControl);
        var fullScreen = $('.fullScreen', playControl);
        var target;

        var t = {
            'play': function (url, title, obj) {
                if (url && url != playVideo.attr('src')) playVideo.prop('src', url);
                titleBar.text(title);
                obj && (target = obj);
                if (playVideo[0].paused) {
                    if(target) playVideo[0].currentTime = target.data('currentTime') || 0
                    playVideo[0].play();
                    _play()
                } else {
                    playVideo[0].pause();
                    _pause()
                }
            }
        }

        function _play() {
            playTip.removeClass('glyphicon-play').addClass('glyphicon-pause').fadeOut();
            playPause.removeClass('playIcon')
        }

        function _pause() {
            playTip.removeClass('glyphicon-pause').addClass('glyphicon-play').fadeIn();
            playPause.addClass('playIcon');
        }

        playVideo[0].volume = 0.4; //初始化音量
        playPause.on('click', play);
        playContent.on('click', play)
        function play(){
            t.play();
        }
        $(d).click(function () {
            volumebar.hide();
        });
        playVideo.on('loadedmetadata', function () {
            duration.text(formatSeconds(playVideo[0].duration));
        });

        playVideo.on('timeupdate', function () {
            var t = playVideo[0].currentTime;
            currentTime.text(formatSeconds(t));
            prbar.css('width', 100 * t / playVideo[0].duration + '%');
            target.data('currentTime', t);
        });
        playVideo.on('ended', _pause);

        $(w).keyup(function (event) {
            event = event || w.event;
            if (event.keyCode == 32)t.play();
            if (event.keyCode == 27) {
                fullScreen.removeClass('cancleScreen');
                playControl.css({
                    'bottom': -48
                }).removeClass('fullControll');
            }
            event.preventDefault();
        });

        //全屏
        fullScreen.on('click', function () {
            if ($(this).hasClass('cancleScreen')) {
                if (d.exitFullscreen) {
                    d.exitFullscreen();
                } else if (d.mozExitFullScreen) {
                    d.mozExitFullScreen();
                } else if (d.webkitExitFullscreen) {
                    d.webkitExitFullscreen();
                }
                $(this).removeClass('cancleScreen');
                playControl.css({
                    'bottom': -48
                }).removeClass('fullControll');
            } else {
                if (playVideo[0].requestFullscreen) {
                    playVideo[0].requestFullscreen();
                } else if (playVideo[0].mozRequestFullScreen) {
                    playVideo[0].mozRequestFullScreen();
                } else if (playVideo[0].webkitRequestFullscreen) {
                    playVideo[0].webkitRequestFullscreen();
                } else if (playVideo[0].msRequestFullscreen) {
                    playVideo[0].msRequestFullscreen();
                }
                $(this).addClass('cancleScreen');
                playControl.css({
                    'left': 0,
                    'bottom': 0
                }).addClass('fullControll');
            }
            return false;
        });
        //音量
        volume.on('click', function (e) {
            e = e || w.event;
            volumebar.toggle();
            e.stopPropagation();
        });
        volumebar.on('click mousewheel DOMMouseScroll', function (e) {
            e = e || w.event;
            _volumeControl(e);
            e.stopPropagation();
            return false;
        });
        progress.mousedown(function (e) {
            e = e || w.event;
            _updatebar(e.pageX);
        });
        //$('.playContent').on('mousewheel DOMMouseScroll',function(e){
        //	volumeControl(e);
        //});
        function _updatebar(x) {
            var maxduration = playVideo[0].duration; //Video
            var positions = x - prbar.offset().left; //Click pos
            var percentage = 100 * positions / progress.width();
            //Check within range
            if (percentage > 100) {
                percentage = 100;
            }
            if (percentage < 0) {
                percentage = 0;
            }

            //Update progress bar and video currenttime
            prbar.css('width', percentage + '%');
            playVideo[0].currentTime = maxduration * percentage / 100;
        }
        //音量控制
        function _volumeControl(e) {
            e = e || w.event;
            var eventype = e.type;
            var delta = (e.originalEvent.wheelDelta && (e.originalEvent.wheelDelta > 0 ? 1 : -1)) || (e.originalEvent.detail && (e.originalEvent.detail > 0 ? -1 : 1));
            var positions = 0;
            var percentage = 0;
            if (eventype == "click") {
                positions = volumeprbar.offset().top - e.pageY;
                percentage = 100 * (positions + volumeprbar.height()) / volumewrap.height();
            } else if (eventype == "mousewheel" || eventype == "DOMMouseScroll") {
                percentage = 100 * (volumeprbar.height() + delta) / volumewrap.height();
            }
            if (percentage < 0) {
                percentage = 0;
                volume.attr('class', 'volume glyphicon glyphicon-volume-off');
            }
            if (percentage > 50) {
                volume.attr('class', 'volume glyphicon glyphicon-volume-up');
            }
            if (percentage > 0 && percentage <= 50) {
                volume.attr('class', 'volume glyphicon glyphicon-volume-down');
            }
            if (percentage >= 100) {
                percentage = 100;
            }
            volumeprbar.css('height', percentage + '%');
            playVideo[0].volume = percentage / 100;
            e.stopPropagation();
            e.preventDefault();
        }

        //秒转时间
        function formatSeconds(value) {
            value = parseInt(value);
            var time;
            if (value > -1) {
                var hour = Math.floor(value / 3600);
                var min = Math.floor(value / 60) % 60;
                var sec = value % 60;
                var day = parseInt(hour / 24);
                if (day > 0) {
                    hour = hour - 24 * day;
                    time = day + "day " + hour + ":";
                } else time = hour + ":";
                if (min < 10) {
                    time += "0";
                }
                time += min + ":";
                if (sec < 10) {
                    time += "0";
                }
                time += sec;
            }
            return time;
        }

        return t;
    }
})(jQuery,window,document)