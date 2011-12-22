<script type="text/javascript" charset="utf-8">
<!--
$(document).ready(function(){
	$("#uploadify").uploadify({
		'uploader': '[+base_path+]js/uploadify/uploadify.swf',
		'script': '[+base_path+]action.php',
		'checkScript': '[+base_path+]check.php',
		'scriptData': {[+params+],[+uploadparams+]},
		'folder': '[+base_url+]assets/galleries/[+content_id+]',
		'multi': true,
		'fileDesc': '[+lang.image_files+]',
		'fileExt': '*.jpg;*.png;*.gif',
		'simUploadLimit': 2,
		'sizeLimit': [+upload_maxsize+],
		'buttonText': '[+lang.select_files+]',
		'cancelImg': '[+base_path+]js/uploadify/cancel.png',
		'onComplete': function(event, queueID, fileObj, response, data) {
            var info = eval('(' + response + ')');
            if (info['result']=='ok') {
				var onlygallery = $.urlParam('onlygallery', location.href)=='1'?"&onlygallery=1":"";
				$('#uploadList').append("<li><div class=\"thbSelect\"><a class=\"select\" href=\"#\">[+lang.select+]</a></div><div class=\"thbButtons\"><a href=\"" + unescape('[+self+]') + "&action=edit&content_id=[+content_id+]&edit=" + info['id'] + onlygallery + "\" class=\"edit\">[+lang.edit+]</a><a href=\"" + unescape('[+self+]') + "&delete=" + info['id'] + "\" class=\"delete\">[+lang.delete+]</a></div><img src=\"" + unescape('[+thumbs+]') + encodeURI(info['filename']) + "\" alt=\"" + info['filename'] + "\" class=\"thb\" /><input type=\"hidden\" name=\"sort[]\" value=\"" + info['id'] + "\" /></li>");
			}	
			else
				alert('[+lang.upload_failed+]: ' + info['msg']);
        },
        'onAllComplete': function(){
            $(".thbButtons").hide();
            $("li").not('.selected').children(".thbSelect").hide();
			if (!$("#uploadList li").length) {
				$("#selectallcontrols").hide();
				$("#sortcontrols").hide();
			}	
        }
	});

	$.urlParam = function(name, link){
		var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(link);
		return (results && results[1]) || 0;
	}

    $('#uploadFiles').click(function(){
        $('#uploadify').uploadifyUpload();
        return false;
    });
    $('#clearQueue').click(function(){
        $('#uploadify').uploadifyClearQueue();
        return false;
    });
	if($('#uploadList').length > 0){
        $(".thbButtons").hide();
        $(".thbSelect").hide();
        $("#uploadList li").live("mouseover", function(){
                $(this).find(".thbButtons").show();
                var sel = $(this).find(".thbSelect");
				if (!sel.hasClass('selected'));
					sel.show();
        });
        $("#uploadList li").live("mouseout", function(){
                $(this).find(".thbButtons").hide();
                if (!$(this).hasClass('selected'))
					$(this).find(".thbSelect").hide();
        });
        $(".thbButtons .delete").live("click", function(event){
            if(confirm('[+lang.delete_confirm+]')){
                $.get($(this).attr('href'));
                $(this).parent().parent('li').remove();
				if (!$("#uploadList li").length) {
					$("#selectallcontrols").hide();
					$("#sortcontrols").hide();
				}	
            }
            return false;
        });
        $(".edit").live("click", function(event){
            var link = $(this).attr("href");
            var overlay = $(this).overlay({
                api: 'true',
                target: '#overlay',
                oneInstance: true,
                onBeforeLoad: function(){
                    $("#overlay .contentWrap").load(link, function(){
                        var keyword_tags = new TagCompleter("keywords", "keyword_tagList", ",");
                    });
                },
                onClose: function(){
                    if($('.newimage').length > 0){
                        window.location.reload();
                    }
                },
                onLoad: function(){
                    $("#cmdsave").click(function(){
                        overlay.close();
                    });
                	$("#newimage").uploadify({
                		'uploader': '[+base_path+]js/uploadify/uploadify.swf',
                		'script': '[+base_path+]action.php',
                		'checkScript': '[+base_path+]check.php',
                		'scriptData': {[+params+], [+uploadparams+], 'edit': $.urlParam('edit',link)},
                		'folder': '[+base_url+]assets/galleries/[+content_id+]',
                		'multi': false,
                		'fileDesc': '[+lang.image_files+]',
                		'fileExt': '*.jpg;*.png;*.gif',
                		'simUploadLimit': 2,
                		'sizeLimit': [+upload_maxsize+],
						'buttonText': '[+lang.browse_file+]',
                   		'cancelImg': '[+base_path+]js/uploadify/cancel.png',
                		'onComplete': function(event, queueID, fileObj, response, data) {
							var info = eval('(' + response + ')');
							if (info['result']=='ok')
								$('.thumbPreview').empty().append('<img class="newimage" src="' + unescape('[+thumbs+]') + encodeURI(info['filename']) + '" alt="' + info['filename'] + '" />');
							else
								alert('[+lang.upload_failed+]: ' + info['msg']);

                        }
               	    });
                    $('#newimageupload').click(function(){
                        $('#newimage').uploadifyUpload();
                        return false;
                    });
                }
            });
            overlay.load();
            return false;
        });
        $(".thbSelect .select").live("click", function(event){
			$(this).closest('li').toggleClass('selected');
            return false;
        });
		$("#selectall").click( function() {
			$("#uploadList li").addClass('selected');
			$("#uploadList li .thbSelect").show();
		});
		$("#unselectall").click( function() {
			$("#uploadList li").removeClass('selected');
			$("#uploadList li .thbSelect").hide();
		});

        $("#uploadList").sortable();

		$.getMode = function(content_id) {
			var ids = [];
			$("#uploadList li.selected").each(function(){
				ids.push($(this).find('input').val());
			});
			if (ids.length>0)
				return {'mode': 'id','action_ids': ids};
			else
				return {'mode': 'contentid', 'action_ids': content_id};
			
		}

		$('#cmdCntDel').click(function(){
			if(confirm('[+lang.delete_indoc_confirm+]')){
				var mode = $.getMode([+content_id+]);
				$.execAction(this, {'action': 'deleteall', 'mode': mode['mode'], 'action_ids': mode['action_ids']});
			}
			return false;
		});
		
		$('#cmdCntRegenerate').click(function(){
			if(confirm('[+lang.regenerate_indoc_confirm+]')){
				var mode = $.getMode([+content_id+]);
				$.execAction(this, {'action': 'regenerateall', 'mode': mode['mode'], 'action_ids': mode['action_ids']});
			}
			return false;
		});

		$('#cmdCntMoveTo').click(function(){
			var overlay = $(this).overlay({
				api: 'true',
				target: '#moveto-popup',
				oneInstance: true,
				closeOnEsc: true,
				closeOnClick: false,
				onLoad: function() {
					top.tree.ca = 'move';
				},
				onClose: function() {
					top.tree.ca = '';
					window.location.reload();
				}
			});
			overlay.load();
			$('#moveto').click( function(){
				var target = $("#movetarget_id").val();
				if (target!=0) {
					var mode = $.getMode([+content_id+]);
					$.post("[+base_path+]action.php", 
						{[+params+], 'action': 'move', 'target': target, 'mode': mode['mode'], 'action_ids': mode['action_ids'].toString()},
						function() {
							overlay.close();
						}
					);
				}
				return false;
			});
		});
	}

});

$(window).load(function() {
	if (!$("#uploadList li").length) {
		$("#selectallcontrols").hide();
		$("#sortcontrols").hide();
	}	
});

top.main.setMoveValue = function(pId, pName) {
	if (pId!=0) {
		$("#movetarget_id").val(pId);
		$('#movetarget_doc').html("Document: <strong>" + pId + "</strong> (" + pName + ")");
	}
}


-->
</script>
