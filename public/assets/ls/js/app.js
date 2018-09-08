String.prototype.escapeHTML = function () {
    return (
        this.replace(/>/g, '&gt;').replace(/</g, '&lt;').replace(/"/g, '&quot;')
    );
};

$(document).ready(function () {
    /* Review */
    $('.card-image').mouseover(function () {
        $('.card-modal').fadeIn(100).toggleClass('active');
    }).mouseout(function () {
        $('.card-modal').fadeOut(100).toggleClass('active');
    });

});

var player;
var cols = {},
    messageIsOpen = false;
$(function () {

    introInit();

    /* ------- Init Perfect Scrollbar ------- */
    initScrollbar('#perfectScroll');
    initScrollbar('#pScrollerMenu');
    initScrollbar('#pScrollerMain');
    initScrollbar('#lScroll');


    // Bind the event.
    $(window).hashchange(function () {

        var hash = location.hash.replace(/^#/, '');
        if (hash === '') {
            $('.trigger-message-close').trigger('click');
        }

    });

    var $curMenu = $('#' + LS_NAV);
    if (!$curMenu.is(':visible')) {
        $curMenu.parent().prev('a').trigger('click');
    }
    $curMenu.addClass('active');

    if ($("#modal-5").length) {
        modalInit();
    }

    var $lazy = $('#lScroll .lazy');
    if ($lazy.length) {
        $lazy.Lazy({
            appendScroll: $('#lScroll')
        });
    }

    var $selectls = $('select');
    if ($selectls.length) {
        $selectls.select2({
            width: '100%',
            allowClear: true
        });
    }

    var $intro = $("[data-step]");
    if ($intro.length) {
        $(document).on("click", "#hiw", function (e) {
            introJs().setOption("showProgress", true).start();
        });
    } else {
        $("#mTutorial").remove();
    }


    initOverlays();

    /* Init the Hashwatcher */
    detectHash();

    var $statsTable = $("#stats-table");
    if ($statsTable.length) {
        $statsTable.DataTable({
            dom: '<"top"f>rt<"bottom"ip>',
            responsive: true,
            'columnDefs': [
                {'orderData': [2], 'targets': [3]},
                {
                    "targets": [2],
                    "visible": false,
                    "searchable": false
                }
            ]
        });
        $statsTable.on("page.dt", function () {
            initTooltips('.tt');
        });
        initTooltips('.tt');
    }
    var $champTable = $("#champ-table");
    if ($champTable.length) {
        $champTable.DataTable({
            dom: '<"top"f>rt<"bottom"ip>',
            responsive: true
        });
    }

    initTooltips('.tt');
});

var $isPlayer = $(".refreshInGamePlayer");
if ($isPlayer.length) {

    setInterval(function () {
        checkRunesInGame($isPlayer, false, true);
    }, 10 * 1000);
    var $playerWindow = $("#playerMain");
    $playerWindow.height($(window).height() - 65);
    var $videoHeight = $playerWindow.height();
    var $videoWidth = $playerWindow.width();

    var options = {
        width: $videoWidth,
        height: $videoHeight,
        channel: $('#streamer-channel').val(),
        playsinline: true
    };
    if ($isPlayer.hasClass('twitch-embed')) {
        player = new Twitch.Embed("playerMain", options);
    } else {
        player = new Twitch.Player("playerMain", options);
    }

    player.setVolume(0.5);

    checkRunesInGame($isPlayer, false, true);

}

$(document).on("click", ".refreshRunes", function (e) {
    checkRunesInGame($isPlayer, false, true);
});


$(document).on("click", ".refreshInGamePlayer", function (e) {
    e.preventDefault();
    checkRunesInGame($(this), true, true);
});

$(document).on('click','.toggle-chat',function(){

    var $click = $(this);

    if($click.hasClass('clicked')){
        return false;
    }

    $click.addClass('clicked').html('<i class="fa fa-spin fa-spinner"></i>');

});

function checkRunesInGame(btn, toggleOpen, forceReload) {

    var $btn = btn;
    var $url = $('#ajax-route-load-perks').val();
    var $toggle = $('.r-foot-check');
    var $ul = $('.footNavUL');
    if ($btn.hasClass('disabled')) {
        return false;
    }

    if (forceReload === true || !$btn.hasClass('inGame')) {
        if (toggleOpen === true) {
            $btn.html('<i class="fa fa-spin fa-refresh"></i> Refreshing...').addClass('disabled').css('background', '#2d2d2d');
        }
        $.post($url)
            .done(function (data) {
                $ul.html(data);
                var $champDiv = $(data).find('#ls-champion');
                if ($champDiv.length) {
                    $btn.html('<i class="fa fa-info-circle"></i> Playing ' + $champDiv.html() + ' - Check Runes')
                        .removeClass('disabled')
                        .addClass('inGame')
                        .css('background', '#82b84f');
                    initTooltips('.tt');
                } else {
                    $btn.html('<i class="fa fa-refresh"></i> Not in Game').removeClass('disabled').css('background', '#a01c1a').removeClass('inGame');
                    if ($toggle.is(':checked')) {
                        $('.closeBar').trigger('click');
                    }
                }
                if (!$toggle.is(':checked') && toggleOpen === true && $btn.hasClass('inGame')) {
                    $toggle.trigger('click');
                    $btn.hide();
                }
            }).fail(function () {
            $btn.html('Error').removeClass('disabled').css('background', '#a01c1a');
        });
    }
}


var $isPlayerVod = $(".refreshInGamePlayerVod");
if ($isPlayerVod.length) {
    checkRunesInGameVod($isPlayerVod, true, true);
}

$(document).on("click", ".refreshRunes", function (e) {
    checkRunesInGameVod($isPlayerVod, false, true);
});

$(document).on("click", ".closeBar", function (e) {
    $('.r-foot-check').trigger('click');
    if ($isPlayer.length) {
        $isPlayer.show();
    }
    if ($isPlayerVod.length) {
        $isPlayerVod.show();
    }
});


$(document).on("click", ".refreshInGamePlayerVod", function (e) {
    e.preventDefault();
    checkRunesInGameVod($(this), true, true);
});

function checkRunesInGameVod(btn, toggleOpen, forceReload) {

    var $btn = btn;
    var $url = $('#ajax-route-load-perks-vod').val();
    var $toggle = $('.r-foot-check');
    var $ul = $('.footNavUL');
    var $match = $btn.attr('data-match');
    if ($btn.hasClass('disabled')) {
        return false;
    }

    var $playerBody = $('#tFrame').contents().find('#video-playback');
    var $ts = $playerBody.find('.player-seek__time');
    console.log($ts);

    if (forceReload === true || !$btn.hasClass('inGame')) {
        if (toggleOpen === true) {
            $btn.html('<i class="fa fa-spin fa-refresh"></i> Refreshing...').addClass('disabled').css('background', '#2d2d2d');
        }
        $.get($url, {
            match: $match
        })
            .done(function (data) {
                $ul.html(data);
                var $champDiv = $(data).find('#ls-champion');
                if ($champDiv.length) {
                    $btn.html('<i class="fa fa-info-circle"></i> Playing ' + $champDiv.html() + ' - Check Runes')
                        .removeClass('disabled')
                        .addClass('inGame')
                        .css('background', '#82b84f');
                    initTooltips('.tt');
                } else {
                    $btn.html('<i class="fa fa-refresh"></i> Not in Game').removeClass('disabled').css('background', '#a01c1a').removeClass('inGame');
                    if ($toggle.is(':checked')) {
                        $('.closeBar').trigger('click');
                    }
                }
                if (!$toggle.is(':checked') && toggleOpen === true && $btn.hasClass('inGame')) {
                    $toggle.trigger('click');
                    $btn.hide();
                }
            }).fail(function () {
            $btn.html('Error').removeClass('disabled').css('background', '#a01c1a');
        });
    }
}

$(document).on("click", ".md-trigger", function () {
    var $btn = $(this);
    var $streamerId = null;
    var $summonerId = null;
    var $summonerName = null;
    var $channel = null;
    if ($btn.hasClass("removeStreamer")) {
        $streamerId = $btn.attr("data-streamer");
        $channel = $btn.attr("data-channel");
        $(".channelName").html($channel);
        $("#streamerRemove").attr("data-streamer", $streamerId);
    }
    if ($btn.hasClass("removeSummoner")) {
        $summonerId = $btn.attr("data-summoner");
        $summonerName = $btn.attr("data-name");
        $streamerId = $btn.attr("data-streamer");
        $(".summonerName").html($summonerName);
        $("#summonerRemove").attr("data-summoner", $summonerId).attr("data-streamer", $streamerId);
    }
    if ($btn.hasClass("reportStreamer")) {
        $streamerId = $btn.attr("data-streamer");
        $channel = $btn.attr("data-channel");
        $(".channelName").html($channel);
        $("#streamerReport").attr("data-streamer", $streamerId);
    }
    if ($btn.hasClass("reportSummoner")) {
        $summonerId = $btn.attr("data-summoner");
        $summonerName = $btn.attr("data-name");
        $streamerId = $btn.attr("data-streamer");
        $(".summonerName").html($summonerName);
        $("#summonerReport").attr("data-summoner", $summonerId).attr("data-streamer", $streamerId);
    }
});
$(document).on("click", ".md-close", function (e) {
    var $btn = $(this);
    $btn.parent().parent().parent().removeClass("md-show");
});
$(document).on("click", "#streamerRemove", function (e) {
    var $btn = $(this);
    var $url = $("#ajax-route-delete-streamer").val();
    var $streamer = $btn.attr("data-streamer");
    $.post($url, {
        streamer: $streamer
    }).done(function (data) {
        openNoty(data.result, data.message);
        if (data.result === "success") {
            $("#sRow_" + $streamer).remove();
        }
    }).fail(function () {
        openNoty("error", "Ajax failed. The administrator was informed about the incident");
    });
});
$(document).on("click", "#summonerRemove", function (e) {
    var $btn = $(this);
    var $url = $("#ajax-route-delete-summoner").val();
    var $summoner = $btn.attr("data-summoner");
    $.post($url, {
        summoner: $summoner
    }).done(function (data) {
        openNoty(data.result, data.message);
        if (data.result === "success") {
            $("#sDiv_" + $summoner).remove();
        }
    }).fail(function () {
        openNoty("error", "Ajax failed. The administrator was informed about the incident");
    });
});
$(document).on("click", "#streamerReport", function (e) {
    var $btn = $(this);
    var $url = $("#ajax-route-report-streamer").val();
    var $streamer = $btn.attr("data-streamer");
    var $reason = $("#reportDetails").val();
    if (!$reason.length) {
        openNoty("warning", "Please give a reason for the report.");
        return false;
    }
    $.post($url, {
        streamer: $streamer,
        reason: $reason
    }).done(function (data) {
        openNoty(data.result, data.message);
    }).fail(function () {
        openNoty("error", "Ajax failed. The administrator was informed about the incident");
    });
});
$(document).on("click", "#summonerReport", function (e) {
    var $btn = $(this);
    var $url = $("#ajax-route-report-summoner").val();
    var $summoner = $btn.attr("data-summoner");
    var $reason = $("#reportDetails").val();
    if (!$reason.length) {
        openNoty("warning", "Please give a reason for the report.");
        return false;
    }
    $.post($url, {
        summoner: $summoner,
        reason: $reason
    }).done(function (data) {
        openNoty(data.result, data.message);
    }).fail(function () {
        openNoty("error", "Ajax failed. The administrator was informed about the incident");
    });
});
$(document).on("mouseenter", ".refreshStreamer", function () {
    var $icon = $(this);
    $icon.addClass("fa-spin");
});
$(document).on("mouseleave", ".refreshStreamer", function () {
    var $icon = $(this);
    $icon.removeClass("fa-spin");
});

$(document).on("click", ".refreshStreamer", function (e) {

    var $btn = $(this);
    var $summoners = $btn.attr('data-summoners');
    var $streamer = $btn.attr("data-streamer");
    var $logo = $("#logo").attr("src");
    var $row = $("#sRow_" + $streamer);
    var $panel = $("#sPanel_" + $streamer);
    var $url = $("#ajax-route-update-streamer-single").val();
    var $containerRefresh = $btn.attr('data-streamer-container');

    var $s = $summoners.split(',');
    var $countUp = 0;

    var $checkString = '';
    var requests = [];
    $.each($s, function (i, v) {
        var $parts = v.split('___');
        var $name = $parts[0];
        var $id = $parts[1];
        $checkString = $checkString + '<span id="s_' + $id + '" class="text-white">' + $name + ': <i class="fa fa-spin fa-spinner"></i></span> | ';

        var request = $.post($url, {
            id: $id
        }).done(function (data) {

            if (data.extra.action === "remove") {
                openNoty(data.result, data.message);
                $row.remove();
                $.each(requests, function (ir, vr) {
                    vr.abort();
                });
                return false;
            }

            if (data.extra.action === 'found') {
                $.each(requests, function (ir, vr) {
                    vr.abort();
                });

                /* Update Container */
                $.post($containerRefresh, {
                    id: $id
                }).done(function (dataC) {
                    $row.replaceWith(dataC);
                    initTooltips('.tt');
                    $panel.unblock();
                }).fail(function () {
                    $panel.unblock();
                });


                return false;
            }

            $countUp++;
            $('#s_' + $id).removeClass('text-white').addClass('text-' + data.extra.iClass).html($name + ': <i class="fa fa-' + data.extra.icon + '"></i>');
            if ($countUp === $s.length) {
                /* Update Container */
                $.post($containerRefresh, {
                    id: $id
                }).done(function (dataC) {
                    $row.replaceWith(dataC);
                    initTooltips('.tt');
                    $panel.unblock();
                }).fail(function () {
                    $panel.unblock();
                });
            }

        }).fail(function () {
            $panel.unblock();
        });

        requests.push(request);

    });

    $panel.block({
        message: '' +
            '   <div class="row">' +
            '       <div class="col col-12">' +
            '           <div class="row">' +
            '               <div class="col col-4 text-right">' +
            '                   <img src="' + $logo + '" style="max-height:85px;">' +
            '               </div>' +
            '               <div class="col col-8 text-left">' +
            '                   <h1 style="margin-top:25px;"><i class="fa fa-spin fa-spinner"></i> Updating Streamer</h1>' +
            '               </div>' +
            '           </div>' +
            '       </div>' +
            '   </div>' +
            '   <br />' +
            '   <div class="row">' +
            '       <div class="col col-12">' +
            $checkString +
            '       </div>' +
            '   </div> ',
        css: {
            backgroundColor: "transparent",
            color: "#fff",
            border: "none",
            width: "100%"
        },
        width: "100%"
    });


});

/* Click on any Listitem */
$(document).on('click', '.clickable', function (e) {

    e.preventDefault();
    window.location.hash = $(this).attr('data-hash');


    var $uId = Math.floor((Math.random() * 10000) + 1);

    $('body').append('<div class="messageFly" id="' + $uId + '"></div>');

    var $message = $('#' + $uId);
    $message.show();

    loadDetails($(this), $uId);

    if (messageIsOpen) {
        if (!$(this).hasClass('innerMessage')) {
            cols.hideMessage();
        }
        setTimeout(function () {
            cols.showMessage();
        }, 300);
    } else {
        cols.showMessage();
    }
    cols.showOverlay();
});


$("#main .clickableLegacy").on("click", function (e) {
    var item = $(this),
        target = $(e.target);
    if (target.is("label")) {
        item.toggleClass("selected");
    } else {
        if (messageIsOpen && item.is(".active")) {
            cols.hideMessage();
            cols.hideOverlay();
        } else {
            if (messageIsOpen) {
                cols.hideMessage();
                item.addClass("active");
                setTimeout(function () {
                    cols.showMessage();
                }, 300);
            } else {
                item.addClass("active");
                cols.showMessage();
            }
            cols.showOverlay();
        }
    }
});
$("#main > .overlay").on("click", function () {
    cols.hideOverlay();
    cols.hideMessage();
    cols.hideSidebar();
});

$(".search-box input").on("focus", function () {
    if ($(window).width() <= 1360) {
        cols.hideMessage();
    }
});
$("#preloader").fadeOut("slow", function () {
    $(this).remove();
});

function introInit() {

    var $video = $('.cd-bg-video-wrapper');

    if ($video.length) {
        var mq = window.getComputedStyle(document.querySelector('.cd-bg-video-wrapper'), '::after').getPropertyValue('content').replace(/"/g, "").replace(/'/g, "");
        if (mq === 'desktop') {
            // we are not on a mobile device
            var videoUrl = $video.data('video'),
                video = $('<video loop><source src="' + videoUrl + '.mp4" type="video/mp4" /><source src="' + videoUrl + '.webm" type="video/webm" /></video>');
            video.appendTo($video);
            video.get(0).play();
        }
    }

}

function modalInit() {
    [].slice.call($(".md-trigger")).forEach(function (el, i) {
        var $modal = $("#" + el.getAttribute("data-modal"));
        el.addEventListener("click", function (ev) {
            $modal.addClass("md-show");
        });
    });
}

$(document).on("click", ".manageSmurf", function (e) {
    var $btn = $(this);
    var $type = $btn.attr('data-type');
    var $smurf = $btn.attr('data-smurf');
    var $streamer = $btn.attr('data-streamer');
    var $region = $btn.attr('data-region');
    var $url = $("#ajax-route-manage-smurf").val();
    var $btnText = $btn.html();
    if ($btn.hasClass("is-disabled")) {
        return false;
    }
    $btn.addClass('is-disabled').html('<i class="fa fa-spin fa-spinner"></i>');
    $.get($url, {
        type: $type,
        smurf: $smurf,
        streamer: $streamer,
        region: $region
    }).done(function (data) {
        openNoty(data.result, data.message);
        if (data.result === "success") {
            $('#row_' + $streamer).remove();
        }
    }).fail(function () {
        openNoty("error", "Ajax failed. The administrator was informed about the incident");
        $btn.removeClass("is-disabled").html($btnText);
    });


});

$(document).on("click", "#submitStreamer", function (e) {
    var $btn = $(this);
    var $channel = $("#channel").val().trim();
    var $platform = $("#platform").val();
    var $url = $("#ajax-route-check-streamer").val();
    if ($btn.hasClass("is-disabled")) {
        return false;
    }
    if ($channel === "") {
        openNoty("error", "Please enter a Channel-Name");
        return false;
    }
    $btn.addClass("is-disabled").html("Checking...");
    $.post($url, {
        platform: $platform,
        channel: $channel
    }).done(function (data) {
        openNoty(data.result, data.message);
        $("#channel").val("").focus();
        $btn.removeClass("is-disabled").html("Check");
    }).fail(function () {
        openNoty("error", "Ajax failed. The administrator was informed about the incident");
        $btn.removeClass("is-disabled").html("Check");
    });
});

function addMasteries(mOffset, mRank, mName, type, row, pos, mDesc) {
    var $masteryPlate = $("#masteries-" + type);
    if (mRank === "0") {
        $masteryPlate.append('<div class="mastery mRow' + row + " " + pos + ' mastery-none">\n    <div class="mContent tt" title="' + mName + "<br /><span style='font-size:12px;'>" + mDesc + '</span>">\n        <div class="icon iconsGrey icon-offset-' + mOffset + ' " data-name="' + mName + '"></div>\n    </div>\n</div>');
    } else {
        $masteryPlate.append('<div class="mastery mRow' + row + " " + pos + '">\n    <div class="mContent tt" title="' + mName + "<br /><span style='font-size:12px;'>" + mDesc + '</span>">\n        <div class="icon iconsActive icon-offset-' + mOffset + ' " data-name="' + mName + '"></div>\n        <div class="rank">' + mRank + "</div>\n    </div>\n</div>");
    }
}

var $streamerAc = $("#streamerAC");
if ($streamerAc.length) {
    var $urlAuto = $("#ajax-route-find-streamer-ac").val();
    $streamerAc.autocomplete({
        valueKey: "title",
        source: [{
            url: $urlAuto + "/%QUERY%",
            type: "remote"
        }],
        visibleLimit: 10,
        autoselect: true,
        minLength: 2
    }).on("selected.xdsoft", function (e, value) {
        $("#streamerId").val(value.id);
    }).keyup(function (event) {
    }).focus();
}

$(document).on("click", ".clickRole", function () {
    var $btn = $(this);
    var $cId = $btn.attr('data-role-id');
    var $url = $('#ajax-route-load-vods-role').val();
    if ($('#role_' + $cId).is(':visible')) {
        $('#role_' + $cId).slideToggle();
        return false;
    }
    if ($btn.hasClass('is-diabled')) {
        openNoty('warning', 'Loading VODs already');
        return false;
    }
    $('.vodToggle').hide();
    $('#role_' + $cId).slideToggle('slow').html('&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-spin fa-spinner"></i>');
    $btn.addClass('is-disabled');


    $.get($url, {
        role: $cId
    }).done(function (data) {
        $btn.removeClass("is-disabled");
        $('#role_' + $cId).html('<table class="table table-condensed" id="tbl_' + $cId + '">\n    <thead>\n    <th class="text-center"><i class="fa fa-comment-o"></i></th>\n    <th class="text-center"><i class="fa fa-calendar-o"></i></th>\n    <th class="text-center"><i class="fa fa-video-camera"></i></th>\n    <th class="text-center"><i class="fa fa-trophy"></i></th>\n    <th class="text-center"><i class="fa fa-location-arrow"></i></th>\n    <th class="text-center"><i class="fa fa-flash"></i> </th>\n    <th class="text-center"><i class="fa fa-eye"></i></th>\n    </thead>\n    <tbody>\n    </tbody>\n</table> ');
        $.each(data.vods.videos, function (i, v) {
            var $opp = '';
            if (v.enemyChampion !== null) {
                $opp = 'vs. ' + v.enemyChampion + '';
            }
            $('#tbl_' + $cId + ' > tbody').append('<tr>\n    <td><img src="/assets/vendor/ls/img/flags/' + v.lang + '.png" /></td>\n    <td>' + v.gameStart + '</td>\n    <td>' + v.channelUser + '</td>\n    <td>' + v.league + '</td>\n    <td>' + v.champion + '</td>\n    <td>' + $opp + '</td>\n    <td><a href="' + v.internalLink + '">WATCH</a></td>\n</tr>');
        });

        initScrollbar('#role_' + $cId);

    }).fail(function () {
        openNoty("error", "Ajax failed. The administrator was informed about the incident");
        $btn.removeClass("is-disabled");
    });
});

$(document).on("click", ".clickChampion", function () {
    var $btn = $(this);
    var $cId = $btn.attr('data-champ-id');
    var $url = $('#ajax-route-load-vods').val();
    var $champPanel = $('#champ_' + $cId);
    if ($champPanel.is(':visible')) {
        $champPanel.slideToggle();
        return false;
    }
    if ($btn.hasClass('is-diabled')) {
        openNoty('warning', 'Loading VODs already');
        return false;
    }
    $('.vodToggle').hide();
    $champPanel.slideToggle('slow').html('&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-spin fa-spinner"></i>');
    $btn.addClass('is-disabled');


    $.get($url, {
        c: $cId
    }).done(function (data) {

        $champPanel.html(data);


        var $table = $('#tableChampion');

        $table.DataTable({
            responsive: true,
            dom: ''
        });

        $('.tt').tooltipster({
            animation: "fade",
            delay: 200,
            theme: ["tooltipster-punk", "tooltipster-ls"],
            contentAsHTML: true
        });

        //$champPanel.perfectScrollbar();


    }).fail(function () {
        openNoty("error", "Ajax failed. The administrator was informed about the incident");
        $btn.removeClass("is-disabled");
    });
});

$(document).on("click", ".clickChampionStreamer", function () {

    var $btn = $(this);
    var $cId = $btn.attr('data-champion');
    if ($('#champ_' + $cId).is(':visible')) {
        $('#champ_' + $cId).slideToggle();
        return false;
    }
    if ($btn.hasClass('is-diabled')) {
        openNoty('warning', 'Loading VODs already');
        return false;
    }
    //$('.champToggle').hide();
    $('#champ_' + $cId).slideToggle('slow').html('<h4><i class="fa fa-spin fa-spinner"></i> Loading Streamers</h4>\n<div class="row" style="border-bottom:1px solid #ccc;padding-top:5px;">\n    <div class="col-12">\n        This may take a while...\n    </div>\n</div>');
    $btn.addClass('is-disabled');

    initMainStreamer('#champ_' + $cId);
});

$(document).on("keyup", "#searchChamp", function () {
    var $search = $(this);
    var $sVal = $search.val().toLowerCase();
    if ($sVal !== "") {
        $(".champRow").hide();
        $('[data-champ*="' + $sVal + '"]').show();
        $('[data-streamer*="' + $sVal + '"]').show();
        $('[data-summoner*="' + $sVal + '"]').show();
    } else {
        $(".champRow").show();
    }
});
$(document).on("keyup", "#searchVod", function () {
    var $search = $(this);
    var $sVal = $search.val().toLowerCase();
    if ($sVal !== "") {
        $(".vodCard").hide();
        $('[data-champ*="' + $sVal + '"]').show();
        $('[data-role*="' + $sVal + '"]').show();
    } else {
        $(".vodCard").show();
    }
});
$(document).on("click", ".showWin", function () {
    var $sW = $(this);
    var $win = $sW.attr('data-win');
    if ($win === "1") {
        $sW.html('<i class="fa fa-check"></i>');
    } else {
        $sW.html('<i class="fa fa-remove"></i>');
    }
});
$(document).on("click", "#wishVod", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var $url = $('#ajax-route-load-vods-wish').val();
    var $html = $btn.html();

    if ($btn.hasClass('is-disabled')) {
        return false;
    }

    $btn.addClass('is-disabled').html('<i class="fa fa-spin fa-spinner"></i> Loading');

    /* Vars */
    var $champions = $('#wChampion');
    var $roles = $('#wRole');
    var $streamers = $('#wStreamer');
    var $enemy = $('#wEnemy');
    var $vodRow = $('#vodRow');

    if (!$champions.val().length && !$roles.val().length && !$streamers.val().length && !$enemy.val().length) {
        $btn.removeClass('is-disabled').html($html);
        openNoty('error', 'Please select at least one criteria');
        return false;
    }


    $vodRow.html('<i class="fa fa-spin fa-spinner"></i>');

    $.get($url, {
        champions: $champions.val(),
        roles: $roles.val(),
        streamers: $streamers.val(),
        enemies: $enemy.val()
    }).done(function (data) {
        $vodRow.html(data);
        $btn.removeClass('is-disabled').html($html);
    }).fail(function () {
        $btn.removeClass('is-disabled').html($html);
    });


});

$(document).on("change", "#cmn-toggle-4", function () {
    var $val = false;
    $('#showLabel').html('&nbsp;<i class="fa fa-spin fa-spinner"></i>');
    if ($(this).is(':checked') === true) {
        $(".showWin").each(function (index) {
            var $win = $(this).attr('data-win');
            if ($win === "1") {
                $(this).html('<i class="fa fa-check"></i>');
            } else {
                $(this).html('<i class="fa fa-remove"></i>');
            }
        });
    } else {
        $('.showWin').html('SPOILER');
    }
    $('#showLabel').html('&nbsp;Spoilers');
});
$(document).on("click", "#submitSummoner", function () {
    var $btn = $(this);
    var $streamer = $("#streamerId").val().trim();
    var $summoner = $("#summoner").val();
    var $region = $("#region").val();
    var $url = $("#ajax-route-check-summoner").val();
    if ($btn.hasClass("is-disabled")) {
        return false;
    }
    if ($summoner === "") {
        openNoty("error", "Please enter a Summoner");
        return false;
    }
    if ($streamer === "") {
        openNoty("error", "Please enter a Streamer (from List)");
        return false;
    }
    $btn.addClass("is-disabled").html("Checking...");
    $.get($url, {
        streamerId: $streamer,
        summoner: $summoner,
        region: $region
    }).done(function (data) {
        openNoty(data.result, data.message);
        $("#streamerAC").val("").focus();
        $("#summoner").val("");
        $btn.removeClass("is-disabled").html("Check");
    }).fail(function () {
        openNoty("error", "Ajax failed. The administrator was informed about the incident");
        $btn.removeClass("is-disabled").html("Check");
    });
});

$(document).on('click', '.trigger-message-close', function () {

    var $mb = $(this).closest('.messageFly');

    if ($('.messageFly').length <= 1) {
        cols.hideMessage();
        $mb.remove();
        history.pushState("", document.title, window.location.pathname);
    } else {
        $mb.animate({left: '9999px'}, function () {
            $mb.remove();
        });
    }

});

/* Toggle Treview */
$(document).on('click', '.toggleTree', function (e) {
    e.preventDefault();

    var $items = $(this).next('.treeMenu');
    var $icon = $(this).children('i');
    if ($items.is(':visible')) {
        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
    $items.toggle(200);

});

function loadDetails(trigger, id) {

    /* DIV */
    var $message = $('#' + id);
    $message.html('<i class="fa fa-spin fa-spinner fa-4x"></i>');

    /* Ajax Call */
    $.get(trigger.attr('data-url')).done(function (data) {
        $message.html(data);

        initOverlays();
        initScrollbar('.subScroll');
        initTableVod('#tableVod');
        initTooltips('.tt');
        initMainStreamer('.show-main-streamers');
    });


}

function initScrollbar(selector) {
    if ($(selector).length) {
        new PerfectScrollbar(selector);
    }
}

function openNoty(result, message) {

    new Noty({
        type: result,
        layout: 'topRight',
        theme: 'mint',
        text: message,
        timeout: 5000,
        progressBar: true,
        closeWith: ['click', 'button'],
        animation: {
            open: 'noty_effects_open',
            close: 'noty_effects_close'
        },
        id: false,
        force: false,
        killer: false,
        queue: 'global',
        container: false,
        buttons: [],
        sounds: {
            sources: [],
            volume: 1,
            conditions: []
        },
        titleCount: {
            conditions: []
        },
        modal: false
    }).show();
}

function initOverlays() {

    cols.showOverlay = function () {
        $('body').addClass('show-main-overlay');
    };
    cols.hideOverlay = function () {
        $('body').removeClass('show-main-overlay');
    };

    cols.showMessage = function () {
        $('body').addClass('show-message');
        messageIsOpen = true;
    };
    cols.hideMessage = function () {
        $('body').removeClass('show-message').removeClass('show-main-overlay');
        messageIsOpen = false;
    };

    cols.showSidebar = function () {
        $('body').addClass('show-sidebar');
    };
    cols.hideSidebar = function () {
        $('body').removeClass('show-sidebar');
    };

    $('.trigger-toggle-sidebar').on('click', function () {
        cols.showSidebar();
        cols.showOverlay();
    });

    // When you click the overlay, close everything
    $('#main > .overlay').on('click', function () {
        cols.hideOverlay();
        cols.hideMessage();
        cols.hideSidebar();
    });
}

function detectHash() {

    var hash = window.location.hash;
    if (!hash.length) {
        return false;
    }
    $("[data-hash=" + hash.replace('#', '') + "]").trigger('click');
}

function closeModal() {
    $('.trigger-message-close').trigger('click');
}

function initTableVod(selector) {
    $(selector).DataTable({
        dom: '<"top"f>rt<"bottom"ip>',
        responsive: true,
        "order": [[1, 'desc']]
    });
}

function initTooltips(selector) {
    if ($(selector).length) {
        var width = 250;
        if ($(selector).hasClass('ttLong')) {
            width = 800;
        }

        $(selector).tooltipster({
            animation: "fade",
            delay: 200,
            debug: false,
            maxWidth: width,
            theme: ["tooltipster-punk", "tooltipster-ls"],
            contentAsHTML: true
        });
    }
}

function initMainStreamer(selector) {
    if ($(selector).length) {

        /* Ajax Call */
        $.post($(selector).attr('data-url'), {
            c: $(selector).attr('data-champion'),
        }).done(function (data) {
            $(selector).html(data);
            initTooltips('.tt');
        });

    }
}