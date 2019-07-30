function halim_Player(episode, server, postid, ep_link){
    if(ep_link != '') {      
        jQuery.ajax({
                url:  ajax_player.url,
                type: 'POST',
                data: {
                   action     : 'halim_play_listsv',
                   nonce      : ajax_player.nonce,               
                   episode    : episode,
                   server     : server,
                   postid     : postid,
                   ep_link    : ep_link
               },      
              success: function(data)
              {      
                jQuery("#halim-player-loader").hide();
                jQuery("#ajax-player").html(data);
            }
        });
    } 
    else 
    {       
        jQuery.ajax({
            url:  ajax_player.url,
            type: 'POST',
            data: {
                action     : 'halim_ajax_player',
                nonce      : ajax_player.nonce,
                episode    : episode,
                server     : server,
                postid     : postid
            },      
            success: function(data)
            {         
                var data = data.replace(/\\/g, "");
                if(data) {
                    jQuery('#mobileModal').modal('hide');
                    jQuery("#halim-player-loader").hide();
                    jQuery("#ajax-player").html(data);
                } 
            }
        });
        halim_get_list_server(episode, server, postid);        
    }
}


function halim_get_list_server(episode, server, post_id) {
    jQuery.ajax({
          url:  ajax_player.url,
          type: 'POST',
          data: {
               action     : 'halim_get_listsv',
               nonce      : ajax_player.nonce,               
               episode    : episode,
               server     : server,
               postid     : post_id
           },      
          success: function(data)
          {      
            if(data != 'NULL') {
                jQuery("#halim-ajax-list-server").html(data);
            } else {
                jQuery("#halim-ajax-list-server").html('');
            }
        }
    });    
}

jQuery(document).ready(function($)
{
    if(typeof halim_cfg != 'undefined' || typeof playerInstance != 'undefined') {

        if($('#ajax-player').length !== 0) {
            if(isMobile.any()) {
             jQuery('body').append('<div class="modal fade" id="mobileModal" tabindex="-1" role="dialog" aria-labelledby="mobileModalLabel" aria-hidden="true"><div class="modal-dialog modal-sm" style="position:relative;background: #000;border: 1px solid #333;padding: 10px;text-align: center;color: #fff;"><p><i class="animate-spin hl-spin4 visible-xs-block"></i> Loading Player...</p></div></div>');       
                jQuery('#mobileModal').modal('show');  
                setTimeout(function(){                
                    jQuery('#mobileModal').modal('hide');  
                }, 5000);           
            }
            halim_Player(halim_cfg.episode, halim_cfg.server, halim_cfg.post_id, '');
            setTimeout(function(){
                var embed = $(document).find('.halim-btn.active').data('embed');
                var type = $(document).find('.halim-btn.active, .halim-episode .active').data('type');
                if(embed == 1 || type == 'facebook'){
                    $("#autonext").hide();
                }else{
                    $("#autonext").show();
                }            
            }, 2000);
        }     
    }
 
    $('body').on('click', '.halim-btn', function()
    {
        var post_id     = $(this).data('post-id');
        var server      = $(this).data('server');
        var episode     = $(this).data('episode');
        var eps_title   = $(this).data('title');
        var embed       = $(this).data('embed');
        $('.halim-btn').removeClass('active');
        $(this).addClass('active');
        halim_Player(episode, server, post_id, ''); 
        // $('[data-toggle="resume"]').popover('hide');
        if(typeof playerInstance != 'undefined') playerInstance.pause();

        $('#halim-player-loader').show().html('<p style="margin-top: 40%;">Loading, please wait...</p>');
        $('html, body').animate({scrollTop: $('#ajax-player').offset().top -10 }, 2000); 
        if(embed == 1){
            $("#autonext").hide();
        } else {
            $("#autonext").show();
        }
        if(history.pushState) {
            var slug = (halim_cfg.type_slug == 'slug-1') ? halim_cfg.server_slug+'-'+server : halim_cfg.server_slug+server;
            history.pushState('', '', halim_cfg.post_url +'-'+halim_cfg.eps_slug+'-'+episode+'-'+slug+'/'); 
            document.title = eps_title;
            $('h1.entry-title').html(eps_title);
        }
    });

    $('body').on('click', '.get-eps', function()
    {
        var episode     = $('.halim-btn.active').data('episode');
        var server      = $('.halim-btn.active').data('server');
        var postid      = $('.halim-btn.active').data('post-id');
        var ep_link     = $(this).data('url');
        if(typeof playerInstance != 'undefined') playerInstance.pause();
        $(this).addClass('active').siblings().removeClass('active');
        $('#halim-player-loader').show().html('<p style="margin-top: 40%;">Please wait...</p>');
        halim_Player(episode, server, postid, ep_link);
    }); 
    
    $("#autonext").on('click', function()
    {
        if($("#autonext-status").text() == 'On'){
            $("#autonext-status").text('Off');
        }
        else {
            $("#autonext-status").text('On');
        }
    }); 
});

    
var halim_add_btn = {
    addToggleLight: function(playerInstance) {
        var playerContainer = playerInstance.getContainer();
        var logoToolTip = $('<div></div>')
            .addClass('player-tooltip')
            .html('Toggle Light');
        var logoDiv = $('<div></div>')
            .addClass('jw-icon jw-icon-inline jw-button-color jw-reset halim-toggle-light halim-icon-toggle-light')
            .append(logoToolTip);
        $(playerContainer).find('.jw-icon-fullscreen').before(logoDiv);
    },
    addResizeBar: function(playerInstance) {
        var playerContainer = playerInstance.getContainer();
        var logoToolTip = $('<div></div>')
            .addClass('player-tooltip')
            .html('Resize Player');
        var logoDiv = $('<div></div>')
            .addClass('jw-icon jw-icon-inline jw-button-color jw-reset halim-resize-bar halim-icon-expand size-small')
            .append(logoToolTip);
        $(playerContainer).find('.jw-icon-fullscreen').before(logoDiv);
    }                                       
}