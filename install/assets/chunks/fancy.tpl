/**
 * fancybox
 * 
 * скрипт fancybox (Jquery подключить отдельно)
 * 
 * @category	chunk
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal @modx_category Js
 * @internal    @overwrite false
 */
 
<script type="text/javascript" src="/assets/js/fancybox/jquery.mousewheel-3.0.6.pack.js"></script>
<script type="text/javascript" src="/assets/js/fancybox/jquery.fancybox.js"></script>
<script>
  $(function(){
      $("a[rel=group]").fancybox({'padding':'3px','transitionIn'	: 'none','transitionOut': 'none'});
  });
</script>
<link rel="stylesheet" type="text/css" href="/assets/js/fancybox/jquery.fancybox.css" media="screen" />