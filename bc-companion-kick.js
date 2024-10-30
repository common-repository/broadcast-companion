(function($) {
    /* trigger when page is ready */
    $(document).ready(function() {
        var streamName = bcktKickID.replace(/\s+/g, '').toLowerCase();
        var apiCall = 'https://kick.com/api/v1/channels/'+streamName ;
        $.ajax({
            url: apiCall,
            type: 'GET',
            success: function(data) {
                var data = JSON.parse(data);
                if (data.livestream) {
                    $('body').addClass('online');
                    $('.cp-header__indicator').addClass('cp-header__indicator--online');
                    $('.button--watch-now').attr('href', 'https://kick.com/' + streamName);
                    $('.button--watch-now').attr('data-username', streamName);
                    $('.button--watch-now').attr('data-id', streamName);
                    $('.cp-header__viewers .cp-header__middle--line-2').text(data.livestream.viewers);
                    $('.cp-header__game-playing .cp-header__middle--line-2').text(data.livestream.categories[0].category.name);
                    $('.cp-nav__game-playing--line-2').text(data.livestream.categories[0].category.name);           
                    getVods()
                }
            },
            error: function(data) {
                console.log('Querying ' + streamName + ' - youtube API error...', data);
            },
            complete: function() {
            }
        }); 

    function getVods() {
        // VODS currently unavailable from Kick
        $('.cp-blog__stream').hide();
        $('.cp-blog__posts').addClass('cp-blog__posts--full-width');
    }

    $(document).on('click', '.cp-modal', function() {
        $('.cp-modal').removeClass('cp-modal--active');
        $('.cp-modal iframe').attr('src', '');
    });

    $(document).on('click', '.button--modal', function(e) {
        e.preventDefault();
        id = $(this).data('id');
        var url = 'https://player.kick.com/'+id;
        $('.cp-modal iframe').attr('src', url);
        $('.cp-modal iframe').attr('allow', 'autoplay');
        $('.cp-modal iframe').attr('autoplay', 'true');
        $('.cp-modal').addClass('cp-modal--active');                
    });

    });
})(jQuery);