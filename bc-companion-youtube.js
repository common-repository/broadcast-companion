(function($) {
    /* trigger when page is ready */
    $(document).ready(function() {
        var apiCall = 'https://www.googleapis.com/youtube/v3/search?part=snippet&channelId='+bcytYouTubeID+'&type=video&eventType=live&key='+bcytApiKey;
        var streamName = bcytYouTubeID;
        $.ajax({
            url: apiCall,
            type: 'GET',
            success: function(data) {
                console.log(data)                
                if (data.items.length) {
                    videoId = data.items[0].id.videoId;
                    $('body').addClass('online');
                    $('.cp-header__indicator').addClass('cp-header__indicator--online');
                    $('.button--watch-now').attr('href', 'https://youtube.com/watch?v=' + videoId);
                    $('.button--watch-now').attr('data-username', streamName);
                    $('.button--watch-now').attr('data-id', videoId);
                    getViewerCount(videoId)
                }
            },
            error: function(data) {
                console.log('Querying ' + streamName + ' - youtube API error...', data);
            },
            complete: function() {
            }
        });



    function getViewerCount(videoId){
        $.ajax({
            url: 'https://www.googleapis.com/youtube/v3/videos?part=snippet,liveStreamingDetails&id='+videoId+'&key='+bcytApiKey,
            type: 'GET',
            success: function(data) {
                console.log(data)
                catId = data.items[0].snippet.categoryId;
                $('.cp-header__viewers .cp-header__middle--line-2').text(data.items[0].liveStreamingDetails.concurrentViewers);
                getCategory(catId)
            },
            error: function(data) {
                console.log('Querying Video' + videoId + ' - youtube API error...', data);
            },
            complete: function() {   
            }
        });
    }

    function getCategory(catId){
        $.ajax({
            url: 'https://www.googleapis.com/youtube/v3/videoCategories?part=snippet&id='+catId+'&key='+bcytApiKey,
            type: 'GET',
            success: function(data) {
                console.log(data)
                $('.cp-header__game-playing .cp-header__middle--line-2').text(data.items[0].snippet.title);
                $('.cp-nav__game-playing--line-2').text(data.items[0].snippet.title);                
                },
            error: function(data) {
                console.log('Querying Category' + catId + ' - youtube API error...', data);
            },
            complete: function() {
            }
        });
    }    

    function getVods() {
        var videoURL = 'https://www.googleapis.com/youtube/v3/search?part=snippet&channelId='+bcytYouTubeID+'&maxResults=6&order=date&type=video&key='+bcytApiKey
        $.ajax({
            url: videoURL,
            type: 'GET',                
            success: function(data) {
                console.log(data)
                var vodThumb, vodURL, vodId, vodTitle, template;
                $('.cp-blog__stream').show();
                $('.cp-blog__posts').removeClass('cp-blog__posts--full-width');
                if (data.items.length) {
                    for (var i = 0; i < 6; i++) {
                        preview = data.items[i].snippet.thumbnails.high.url;
                        preview = preview.replace("hqdefault", "maxresdefault");
                        vodId = data.items[i].id.videoId;
                        vodTitle = data.items[i].snippet.title;
                        template = '<div class="cp-blog__vods-tile"><a class="cp-blog__vods-link button--modal" href="https://youtube.com/watch?v=' + vodId + '" target="_blank" data-username="'+streamName+'" data-id="'+vodId+'"><img class="cp-blog__vods-image" src="' + preview + '" /><h4 class="cp-blog__vods-title">' + vodTitle + '</h4><div class="cp-blog__vods-overlay"><span class="icon-play"></span></div></a></div>';
                        $('.cp-blog__vods').append(template);
                    }
                } else {
                    console.log('no vods found, hiding vod section...');
                    $('.cp-blog__stream').hide();
                    $('.cp-blog__posts').addClass('cp-blog__posts--full-width');
                }
            },
            error: function(data) {
                console.log('no vods found, hiding vod section...');
                $('.cp-blog__stream').hide();
                $('.cp-blog__posts').addClass('cp-blog__posts--full-width');
            }
        });
    }

    if ($('.cp-blog__vods').length) {
        getVods();
    }

    $(document).on('click', '.cp-modal', function() {
        $('.cp-modal').removeClass('cp-modal--active');
        $('.cp-modal iframe').attr('src', '');
    });

    $(document).on('click', '.button--modal', function(e) {
        e.preventDefault();
        id = $(this).data('id');
        var url = 'https://www.youtube.com/embed/'+id;
        $('.cp-modal iframe').attr('src', url);
        $('.cp-modal iframe').attr('allow', 'autoplay');
        $('.cp-modal iframe').attr('autoplay', 'true');
        $('.cp-modal').addClass('cp-modal--active');                
    });

    });
})(jQuery);