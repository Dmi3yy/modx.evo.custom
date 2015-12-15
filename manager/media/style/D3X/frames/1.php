<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
header("X-XSS-Protection: 0");
$_SESSION['browser'] = (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 1')!==false) ? 'legacy_IE' : 'modern';
$mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';
if(!isset($modx->config['manager_menu_height'])) $modx->config['manager_menu_height'] = '70';
if(!isset($modx->config['manager_tree_width']))  $modx->config['manager_tree_width']  = '260';
$modx->invokeEvent('OnManagerPreFrameLoader',array('action'=>$action));
?>
<!DOCTYPE html>
<html <?php echo (isset($modx_textdir) && $modx_textdir ? 'dir="rtl" lang="' : 'lang="').$mxla.'" xml:lang="'.$mxla.'"'; ?>>
<head>
    <title><?php echo $site_name?> - (MODX CMS Manager)</title>
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx_manager_charset?>" />
    <link href="media/style/D3X/1.css" rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Ubuntu&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="../assets/plugins/managermanager/js/jquery-1.9.1.min.js"></script>
</head>
<body id="body">

    <div id="mobile_width"></div>
    <div id="mainMenu" class="panel">
        <iframe name="mainMenu" src="index.php?a=1&amp;f=menu" scrolling="no" frameborder="0" noresize="noresize"></iframe>
        <div id="resizer2">
            <a id="hideTopMenu" class="panel_toggl"></a>
        </div>
    </div>

    <div id="tree" class="panel">
        <iframe name="tree" src="index.php?a=1&amp;f=tree" scrolling="no" frameborder="0" onresize="mainMenu.resizeTree();"></iframe>
        <div id="resizer">
            <a id="hideMenu"  class="panel_toggl"></a>
        </div>
    </div>

    <div id="main">
        <iframe name="main" id="mainframe" src="index.php?a=2" scrolling="auto" frameborder="0" onload="if (mainMenu.stopWork()) mainMenu.stopWork(); scrollWork();"></iframe>
    </div>




    <!--<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js'></script>-->
    <script language="JavaScript" type="text/javascript">

        var _mainMenu_H = <?php echo(!isset($modx->config['manager_menu_height']) ? $modx->config['manager_menu_height'] : "70"); ?>;
        var _tree_W = localStorage.getItem('_tree_W');
//        console.log(' storage= ' + _tree_W);
        if (_tree_W === undefined) {
            _tree_W = <?php echo(!isset($modx->config['manager_tree_width']) ? $modx->config['manager_tree_width'] : "260"); ?>;
        }
        var _dragElement;
        var _oldZIndex = 999;
        var _on;
        InitDragDrop();

        function InitDragDrop() {
            document.getElementById('resizer').onmousedown = OnMouseDown;
            document.onmouseup = OnMouseUp;
        }

        function OnMouseDown(e) {
            if (e == null) e = window.event;
            _dragElement = e.target != null ? e.target : e.srcElement;

                if ((e.button == 1 && window.event != null || e.button == 0) && _dragElement.id == 'resizer') {
//                document.body.className = "drag";
                document.getElementById('mobile_width').className = "drag";
                document.onmousemove = OnMouseMove;
                document.body.focus();
                document.onselectstart = function () {
                    return false
                };
                _dragElement.ondragstart = function () {
                    return false
                };
                return false
            }
        }

        function ExtractNumber(value) {
            var n = parseInt(value);
            return n == null || isNaN(n) ? 0 : n
        }

        function OnMouseMove(e) {
            if (e == null) var e = window.event;
            if (e.clientX > 0) var _on = 'on';
            _dragElement.style.left = e.clientX + 'px';
            document.getElementById('tree').style.width = e.clientX + 'px';
            document.getElementById('main').style.left = e.clientX + 'px'
        }

        function OnMouseUp(e) {
            if (_dragElement != null) {
                localStorage.setItem('_tree_W', e.clientX);
                _dragElement.ondragstart = null;
                _dragElement = null;
//                document.body.className = "";
                document.getElementById('mobile_width').className = "";
                document.onmousemove = null;
                document.onselectstart = null
            }
        }

        //save scrollPosition
        function getQueryVariable(variable, query) {
            var vars = query.split('&');
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split('=');
                if (decodeURIComponent(pair[0]) == variable) {
                    return decodeURIComponent(pair[1]);
                }
            }
        }

        function scrollWork() {
            var frm = document.getElementById("mainframe").contentWindow;
            currentPageY = localStorage.getItem('page_y');
            pageUrl = localStorage.getItem('page_url');
            if (currentPageY === undefined) {
                localStorage.setItem('page_y') = 0;
            }
            if (pageUrl === null) {
                pageUrl = frm.location.search.substring(1);
            }
//            console.log(pageUrl +' '+ frm.location.search.substring(1));
            if ( getQueryVariable('a', pageUrl) == getQueryVariable('a', frm.location.search.substring(1)) ) {
                if ( getQueryVariable('id', pageUrl) == getQueryVariable('id', frm.location.search.substring(1)) ){
                    frm.scrollTo(0,currentPageY);
                }
            }

            frm.onscroll = function(){
                if (frm.pageYOffset > 0) {
                    localStorage.setItem('page_y', frm.pageYOffset);
                    localStorage.setItem('page_url', frm.location.search.substring(1));
                }
            }        
        }
        //===== jQuery
        $( document ).ready(function() {

            var mobile_width = $('#mobile_width').width();

            $('<a class="panel_toggl" id="hideTopMenu2"></a>').appendTo('#body'); // append button with fixed position for Safari iOs 
            function check_toggled(toggl){
                //console.log($('body').width() + ' - ' + mobile_width);
                var panel = $(toggl).closest('.panel');
                if($('body').width() > mobile_width){
                    panel.addClass('on');
                    $('#body').removeClass('mobile');
                }else{
                    panel.removeClass('on').attr('style', '');
                    $('#body').addClass('mobile');
                    // $('#tree, #tree iframe').height($('#body').height());
                }
            }
            check_toggled('.panel_toggl');

            $(window).on('resize orientationchange touchstart', function(){
                check_toggled('#body:not(.mobile) .panel_toggl');
                
                setTimeout( function(){ 
                    var bodyH = $('#body').height();
                    $('#tree').height(bodyH);
                }, 250 );
            });

            //----ios fixed 
            var deviceAgent = navigator.userAgent.toLowerCase();
            var iOS = navigator.platform.toLowerCase();
            var agentID = iOS.match(/(iphone|ipod|ipad)/);
            if(agentID){
                //$(window).on("touchmove", function(event) {
                    //var e = event.originalEvent;
                    //console.log('touch ' + e.touches);
                $('body').addClass('ios');
                //});
            }
            
            $('.panel_toggl').on('click', function(){
                
                
                $(this).closest('.panel').toggleClass('on');
                if($(this).is('#hideMenu')){
                    $('#tree, #main, #resizer').attr('style', '');
                }

                if($(this).is('#hideTopMenu')){ // hideTopMenu
                    if($('#body').hasClass('mobile')){
                        var topMenu = $('#mainMenu > iframe').contents().find("#topMenu");
                        
                        $('#mainMenu.on iframe').css('height',  topMenu.height());
                        $('#mainMenu.on').css('height', window.innerHeight);
                    }
                }

                if($(this).is('#hideTopMenu2')){// hideTopMenu close (added to fix button position when TopMenu is open)
                    $('#mainMenu').toggleClass('on');

                    $('#mainMenu iframe').attr('style', '');
                    $('#mainMenu').attr('style', '');
                    // $('body').toggleClass('fixed');
                }
                $('body').toggleClass('fixed');
            });

            // $('body.mobile #tree iframe').load(function(){
            //     console.log('sss');
            //     $(this).contents().find('#treeRoot .treeNode').click(function(){                   
            //         console.log($(this).attr('id') + ' ttt');
            //         $('#hideMenu').trigger('click');
            //     })
            // });
            $('body.mobile #main iframe').load(function(){
                $('#tree.on #hideMenu').trigger('click');
            });
            $('body.mobile #mainMenu iframe').load(function(){
                //console.log('iframe ' + $(this).contents().find('#nav').width());
                $(this).contents().find(' #nav .subnav a').click(function(){
                    $( "#hideTopMenu" ).click();
                    $('body').removeClass('fixed');
                });
            });
            //------------ iphone tap
            $(".panel_toggl").on('touchstart', function (e) {
             $(this).trigger('click');
             e.preventDefault();
            });
        });
    </script>
    <?php
    $modx->invokeEvent('OnManagerFrameLoader',array('action'=>$action));
    ?>
<div id="mask_resizer"></div>
</body>
</html>
<?php
//