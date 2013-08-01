/**
 * fancybox
 * 
 * скрипт fancybox (Jquery подключить отдельно)
 * 
 * @category	chunk
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal @modx_category Js
 * @internal    @installset base
 * @internal    @overwrite false
 */
 
<script type="text/javascript" src="/assets/js/fancybox/jquery.mousewheel-3.0.6.pack.js"></script>
<script type="text/javascript" src="/assets/js/fancybox/jquery.fancybox.js"></script>
<script>
  $(function(){
       $("a[rel=fancy]").fancybox({'transitionIn': 'none','transitionOut': 'none', 'nextEffect': 'none', 'prevEffect': 'none'});
  });
</script>
<link rel="stylesheet" type="text/css" href="/assets/js/fancybox/jquery.fancybox.css" media="screen" />