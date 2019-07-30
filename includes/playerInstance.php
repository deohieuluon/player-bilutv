<?php 

    function playerInstance($jwplayer_key, $sources, $image, $sharing_box, $player_autoplay, $skinPlayer, $logoPlayer, $tracks, $ad_code) 
    { 
        ?>
        <meta name="referrer" content="no-referrer" />
        <div id="halim-player"></div>
        <script>
            var playerInstance = jwplayer("halim-player");
            jQuery(document).ready(function(){           
                playerInstance.setup({
                    /*key: "<?php echo $jwplayer_key; ?>",*/
                    primary: "html5",
                    sources: <?php echo $sources; ?>,
                    image: "<?php echo $image; ?>",
                    width: "100%",
                    height: "100%",
                    aspectratio: "16:9",
                    playbackRateControls: true,   
                    displayPlaybackLabel: true,       
                    fullscreen: true, 
                    <?php echo $sharing_box; ?>
                    autostart: "<?php echo $player_autoplay; ?>",
                    <?php echo $skinPlayer.$logoPlayer.$tracks.$ad_code; ?>     
                });  
                //var resumeId = encodeURI('<?php echo md5($sources); ?>');
                //HaLim.resume_video(resumeId, playerInstance);
            });
        </script>
    <?php
    }