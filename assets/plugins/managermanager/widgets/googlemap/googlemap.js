var $j = jQuery.noConflict();


function googlemap(id,defaultGeoLoc) {
	mapContainerId = "map_canvas_"+id;
	// google maps js is loaded async, so we (loop) wait for it
	if (typeof(GMap2) === 'undefined') {
		setTimeout('googlemap("'+id+'","'+defaultGeoLoc+'");', 200);
	}
	else {
		$j("#"+id).after("<div id='"+mapContainerId+"' style='width: 500px; height: 300px'>Loading map, please wait...</div>");
		$j("#"+mapContainerId).data("googlemap_tvId",id);
		$j("#"+mapContainerId).data("googlemap_defaultGeoLocation",defaultGeoLoc);
		StartGoogleMaps(mapContainerId);
	}
}


function StartGoogleMaps(mapContainerId) {
  if (GBrowserIsCompatible()) {
  
	var googleBarOptions = {
		onGenerateMarkerHtmlCallback : function(selectedMarker, div, result) { 
			var Pos = selectedMarker.getLatLng();
			marker.setLatLng(Pos);
			if (!initOverlay) map.addOverlay(marker);
			$j("#"+tvId).val(Pos.lat() + ',' + Pos.lng());
			div.innerHTML = "Location saved!"; return div; 
		},
		suppressInitialResultSelection : false,
		showOnLoad : true,
		searchFormHint : "address / location"
	};
  
	var mapOptions = {
		googleBarOptions : googleBarOptions
	  };

	var map = new GMap2(document.getElementById(mapContainerId),mapOptions);
	map.setUIToDefault();
	map.disableDoubleClickZoom(); 
	map.enableGoogleBar();

	
	var tvId = $j("#"+mapContainerId).data("googlemap_tvId");
	var defaultGeoLoc = $j("#"+mapContainerId).data("googlemap_defaultGeoLocation");
	
	var geoLoc;
	var initOverlay = false;
	if ($j("#"+tvId).val() != '') {		// TV contains a value already?
		geoLoc = $j("#"+tvId).val().split(',');
		initOverlay = true;
	}
	else {
		geoLoc = (defaultGeoLoc != '')? defaultGeoLoc.split(',') : new Array(52.5,13.5);	// get default from mm_rules, otherwise head to berlin
	}
	

	var center = new GLatLng(geoLoc[0], geoLoc[1]);
	map.setCenter(center, 13); 
	var marker = new GMarker(center, {draggable: true});
	if (initOverlay) map.addOverlay(marker);

	// double click listener - sets marker
	GEvent.addListener(map, "dblclick", function(mark,point) {
			marker.setLatLng(point);
			if (!initOverlay) map.addOverlay(marker);
			$j("#"+tvId).val(point.lat() + ',' + point.lng());
	});

	// drag marker listeners
	GEvent.addListener(marker, "dragstart", function() { map.closeInfoWindow();	});
	GEvent.addListener(marker, "dragend", function(point) {  $j("#"+tvId).val(point.lat() + ',' + point.lng()); });

  }
}
