function openInfoWindow(o){return google.maps.event.trigger(markers[o],"click"),googlemap.panTo(markers[o].getPosition()),googlemap.fitBounds(markers[o].getPosition()),googlemap.setZoom(12),!1}function wpsl_after_render(){}function wpsl_click_marker(){}function wpsl_no_results(){}function wpsl_error(){}function wpsl_success(){}var markers=[],googlemap="",active_form="",formatted_address="";jQuery(function(o){function e(e){var t=".wpsl-map",s=".wpsl-results";if(o(active_form).siblings("#widget").length<1){if("."===wpsl_locator_options.mapcont.charAt(0))var t=o(e).find(wpsl_locator_options.mapcont);else var t=o(wpsl_locator_options.mapcont);if("."===wpsl_locator_options.resultscontainer.charAt(0))var s=o(e).find(wpsl_locator_options.resultscontainer);else var s=o(wpsl_locator_options.resultscontainer)}else var s=o(e).find(s);return formelements={parentdiv:o(e),errordiv:o(e).find(".wpsl-error"),map:t,results:s,distance:o(e).find(".distanceselect"),address:o(e).find(".address"),latitude:o(e).find(".latitude"),longitude:o(e).find(".longitude"),unit:o(e).find(".unit")}}function t(e){var t=o(e.address).val();geocoder=new google.maps.Geocoder,geocoder.geocode({address:t},function(t,r){if(r==google.maps.GeocoderStatus.OK){var a=t[0].geometry.location.lat(),l=t[0].geometry.location.lng();formatted_address=t[0].formatted_address,o(e.latitude).val(a),o(e.longitude).val(l),s(e)}else wpsl_error(wpsl_locator.notfounderror,active_form),o(e.errordiv).text(wpsl_locator.notfounderror).show(),o(e.results).hide()})}function s(e){o.ajax({url:wpsl_locator.ajaxurl,type:"post",datatype:"json",data:{action:"locate",address:o(e.address).val(),formatted_address:formatted_address,locatorNonce:wpsl_locator.locatorNonce,distance:o(e.distance).val(),latitude:o(e.latitude).val(),longitude:o(e.longitude).val(),unit:o(e.unit).val()},success:function(t){"error"===t.status?(wpsl_error(t.message,active_form),o(e.errordiv).text(t.message).show(),o(e.results).hide()):(wpsl_success(t.result_count,t.results,active_form),r(t,e))}})}function r(e,t){if(e.result_count>0){var s=1===e.result_count?wpsl_locator.location:wpsl_locator.locations,r="<h3>"+e.result_count+" "+s+" "+wpsl_locator.found_within+" "+e.distance+" "+e.unit+" of "+e.formatted_address+"</h3><ul>";for(i=0;i<e.results.length;i++){r=r+"<li data-result="+i+"><strong>",r=r+'<a href="'+e.results[i].permalink+'">',r+=e.results[i].title,r+="</a></strong><br />",r=r+"<em>"+wpsl_locator.distance+": "+e.results[i].distance+" "+e.unit+"</em><br />",e.results[i].address&&(r=r+e.results[i].address+"<br />"+e.results[i].city+", "+e.results[i].state+" "+e.results[i].zip);var l=e.results[i].phone,n=e.results[i].website;l&&(r=r+"<br />"+wpsl_locator.phone+": "+l),n&&(r=r+'<br /><a href="'+n+'" target="_blank">'+n+"</a>"),r+='<br /><a href="#" class="infowindow-open map-link" onClick="event.preventDefault(); openInfoWindow('+i+');">'+wpsl_locator.showonmap+"</a>",r+="</li>"}r+="</ul>",o(t.results).removeClass("loading").html(r),o(t.map).show(),o(t.zip).val("").blur(),a(e,t),wpsl_after_render(active_form)}else wpsl_no_results(e.zip,active_form),o(t.errordiv).text("No results found.").show(),o(t.results).hide()}function a(e,t){markers=[];var s=wpsl_locator.mapstyles,r=o(t.map)[0];if("undefined"!=typeof wpsl_locator_options)var a="show"===wpsl_locator_options.mapcontrols?!1:!0;else var a=!1;if("undefined"!=typeof wpsl_locator_options)var l=google.maps.ControlPosition[wpsl_locator_options.mapcontrolsposition];else var l=TOP_LEFT;var n,i,p,d=wpsl_locator.mappin?wpsl_locator.mappin:"",c=new google.maps.LatLngBounds,u={mapTypeId:"roadmap",mapTypeControl:!1,zoom:8,styles:s,panControl:!1,disableDefaultUI:a,zoomControlOptions:{style:google.maps.ZoomControlStyle.SMALL,position:l}},m=[],f=new google.maps.InfoWindow;n=new google.maps.Map(r,u);for(var p=0,_=e.results.length;_>p;p++){var g=e.results[p].title,w=e.results[p].latitude,v=e.results[p].longitude,h=e.results[p].permalink,k=[g,w,v,h];m.push(k)}for(p=0;p<m.length;p++){var y=new google.maps.LatLng(m[p][1],m[p][2]);c.extend(y),i=new google.maps.Marker({position:y,map:n,title:m[p][0],icon:d}),google.maps.event.addListener(i,"click",function(o,e){return function(){f.setContent("<h4>"+m[e][0]+'</h4><p><a href="'+m[e][3]+'">'+wpsl_locator.viewlocation+"</a></p>"),f.open(n,o),wpsl_click_marker(o,e,active_form)}}(i,p)),markers.push(i),n.fitBounds(c)}var b=google.maps.event.addListener(n,"bounds_changed",function(){google.maps.event.removeListener(b)});googlemap=n}o(".wpslsubmit").on("click",function(s){s.preventDefault();var r=o(this).parents(".simple-locator-form");active_form=r;var a=e(r);o(a.errordiv).hide(),o(a.map).hide(),o(a.results).empty().addClass("loading").show(),t(a)})});