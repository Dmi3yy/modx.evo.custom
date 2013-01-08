addComment = {
	moveForm : function(d, f) {
		var m = this,
		a, h = m.I("comment-"+d+"-"+f), b = m.I("respond-"+d), l = m.I("cancel-comment-link-"+d), j = m.I("comment-parent-"+d); 
		if(!h ||!b ||!l ||!j) {return}
		m.respondId = "respond-"+d;
		if(!m.I("temp-form-div")) {
			a = document.createElement("div");
			a.id = "temp-form-div";
			a.style.display = "none";
			b.parentNode.insertBefore(a, b)
		}
		h.parentNode.insertBefore(b, h.nextSibling);
		j.value = f;
		l.style.display = "";
		l.onclick = function() {
			var n = addComment,
			e = n.I("temp-form-div"),
			o = n.I(n.respondId);
			if(!e ||!o) {return}
			n.I("comment-parent-"+d).value = "0";
			e.parentNode.insertBefore(o, e);
			e.parentNode.removeChild(e);
			this.style.display = "none";
			this.onclick = null;
			return false
		};
		try {m.I("content-"+d).focus()}
		catch(g) {}
		return false
	},
	I : function(a) {
		return document.getElementById(a)
	}
};