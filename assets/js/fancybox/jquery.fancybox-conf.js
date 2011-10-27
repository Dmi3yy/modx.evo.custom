		$(document).ready(function() {
		
			$("a[rel=example_group]").fancybox({
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'titlePosition' 	: 'over',
				'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
					return '<span id="fancybox-title-over">Фото ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
				}
			});
			$(".various1").fancybox({
				'titlePosition'		: 'inside',
				'transitionIn'		: 'none',
				'transitionOut'		: 'none'
			});
			
			$(".various2").fancybox({
				'modal' : true
			});
			
			$(".various3").fancybox({
				ajax : {
					type	: "POST",
					data	: 'mydata=test'
				}
			});

			$(".various4").fancybox({
				ajax : {
					type	: "POST"
				}
			});
					
			$(".various5").fancybox({
				'width'				: '75%',
				'height'			: '75%',
				'autoScale'     	: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
			});
			
			$(".various6").fancybox({
				'padding'           : 0,
				'autoScale'     	: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none'
			});
			
		});
