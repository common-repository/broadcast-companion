(function($) {
    /* trigger when page is ready */
    $(document).ready(function() {
        var streamName = bcTwitchUsername;
        var streamId = bcTwitchId;
        var clientId = bcTwitchClientId;
        var clientAuth = bcTwitchClientAuthToken;
        var video = bcVideoSettings;
        var period = bcClipPeriod;
        var periodDate = bcClipPeriodDate;
        getStreamStatus();

        // function to retrieve the stream status (online/offline), given a user ID.
        function getStreamStatus(){
            xhr = new XMLHttpRequest();
            xhr.open("GET", "https://api.twitch.tv/helix/streams?user_login="+streamName);
            xhr.setRequestHeader("Client-id", clientId);
            xhr.setRequestHeader("Authorization", "Bearer "+clientAuth);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    var data = JSON.parse(xhr.responseText)
                    if (data.data && data.data[0].type == 'live') {
                        var game = data.data[0].game_name;
                        var viewers = data.data[0].viewer_count;
                        var name = data.data[0].user_name;
                        $('body').addClass('online');
                        $('.cp-header__indicator').addClass('cp-header__indicator--online');
                        $('.cp-header__game-playing .cp-header__middle--line-2').text(game);
                        $('.cp-nav__game-playing--line-2').text(game);
                        $('.cp-header__viewers .cp-header__middle--line-2').text(viewers);
                        $('.button--watch-now').attr('href', 'https://player.twitch.tv/?channel=' + name);
                        $('.cp-masthead').addClass('cp-masthead--embed-active');
                        console.log('[Broadcast Companion] - Querying ' + name + ' - streamer online...');
                        if (bcTwitchEmbed == 1) {
                            $('.cp-masthead').prepend('<div class="cp-masthead__embed" id="twitch-embed-masthead" style="position:absolute;top:0;left:0;right:0;bottom:0;width:100%;height:100%;"></div>');
                            $('.cp-masthead__wrapper').hide();
                            $('.cp-nav__bottom').hide();
                            embedStream(streamName);
                        }
                    } else {
                        console.log('[Broadcast Companion] - Querying ' + streamName + ' - Streamer offline...');
                    }                    
                }
            };
            xhr.send();
        }

        // function to retrieve the twitch top clips (vods), given a twitch username.
        function getVods() {
            if (video == 'clips') {
                var videoURL = 'https://api.twitch.tv/helix/clips?broadcaster_id='+streamId+periodDate;
            } else if (video == 'highlights') {
                var videoURL = 'https://api.twitch.tv/helix/videos?user_id='+streamId+'&type=highlight'
            } else if (video == 'past-broadcasts') {
                var videoURL = 'https://api.twitch.tv/helix/videos?user_id='+streamId+'&type=archive'
            }
            xhr2 = new XMLHttpRequest();
            xhr2.open("GET", videoURL);
            xhr2.setRequestHeader("Client-id", clientId);
            xhr2.setRequestHeader("Authorization", "Bearer "+clientAuth);
            xhr2.onreadystatechange = function () {
                if (xhr2.readyState === 4) {
                    var data = JSON.parse(xhr2.responseText)
                    console.log(data.data)
                    var vods = data.data;
                    var vodCount = vods.length;
                    if (vodCount < 6) {vodCount = vodCount} else {vodCount = 6}
                    if (vods.length > 0) {
                        $('.cp-blog__stream').show();
                        $('.cp-blog__posts').removeClass('cp-blog__posts--full-width');
                        for (var i = 0; i < vodCount; i++) {
                            if (video == 'clips') {
                                    preview = vods[i].thumbnail_url;
                                    vodEmbed = vods[i].embed_url + '&parent=' + document.location.host;
                                } else if (video == 'highlights' || video == 'past-broadcasts') {
                                    preview = vods[i].thumbnail_url;
                                    preview = preview.replace('%{width}', '480')
                                    preview = preview.replace('%{height}', '272')
                                    vodEmbed = 'https://player.twitch.tv/?video='+vods[i].id+'&parent='+document.location.host;                                
                                } else {
                                    vodEmbedUrl = vods[i].url;
                                    vodEmbed = vods[i].embed_url+'&parent='+document.location.host;                                
                                    if (vods[i].thumbnail_url) {
                                        preview = vods[i].thumbnail_url;
                                    } else {
                                        preview = '';
                                    }
                                }                        
                                if (preview == '') {
                                    preview = swPlaceholder;
                                }
                                vodTitle = vods[i].title;
                                vodTemplate = '<div class="cp-blog__vods-tile"><a class="cp-blog__vods-link button--modal" href="'+vodEmbed+'" target="_blank"><img class="cp-blog__vods-image" src="' + preview + '" /><h4 class="cp-blog__vods-title">' + vodTitle + '</h4><div class="cp-blog__vods-overlay"><span class="icon-play"></span></div></a></div>';
                                $('.cp-blog__vods').append(vodTemplate);
                            }
                    } else {
                        console.log('no vods found, hiding vod section...');
                        jQuery('.cp-blog__stream').hide();
                        jQuery('.cp-blog__posts').addClass('cp-blog__posts--full-width');
                    }                 
                }
            };
            xhr2.send();
        }

        // call getVods if the vods section is on the page.
        if ($('.cp-blog__vods').length) {
            getVods();
        }

        // function to embed the stream in the masthead.
        var embedStream = function(name){
            console.log('embedding masthead stream - '+name);
            theme = 'dark';
            if (bcTwitchEmbedChat == '1') {
                layout = 'video-and-chat';
            } else {
                layout = 'video';
            }
            new Twitch.Embed('twitch-embed-masthead', {
                width: '100%',
                height: '100%',
                channel: name,
                theme: theme,
                layout: layout
            });
        };      

        // remove iframe when clicked.
        $(document).on('click', '.cp-modal', function() {
            $('.cp-modal').removeClass('cp-modal--active');
            $('.cp-modal iframe').attr('src', '');
        });

        // setup iframe when clicked.
        $(document).on('click', '.button--modal', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            if (!url.includes("parent=")) {
                url = url + "&parent=" + document.location.host;
            }
            $('.cp-modal iframe').attr('src', url);
            $('.cp-modal').addClass('cp-modal--active');
        });
    });
})(jQuery);