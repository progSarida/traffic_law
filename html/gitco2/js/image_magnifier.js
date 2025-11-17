function magnifyImage(popup, thumbnail)	{
	
	var ratio = {
		height: popup.height / thumbnail.height,
		width: popup.width / thumbnail.width
	};
	var thumb_url = thumbnail.src.replace(/\'/g, "\\\'");
	var popup_url = popup.src.replace(/\'/g, "\\\'")
	
	var thumbStyle = {
		"background-image" : "url('" + thumb_url + "')",
		"height" : thumbnail.height,
		"width" : thumbnail.width
	}
	var popupStyle = {
		"background-image" : "url('" + popup_url + "')",
		"height" : thumbnail.height,
		"width" : thumbnail.width
	}

	$(".popup").css(popupStyle);
	$(".thumbnail").css(thumbStyle);
	
	$("div.image-magnify:last div.thumbnail").mouseenter(
		function(e) {
			$(this).children("div.popup").css("display", "block");
			$("#thumbnail_image").hide();
		}
	)
	.mouseleave(
		function() {
			$(this).children("div.popup").css("display", "none");
			$("#thumbnail_image").show();
		}
	)
	.mousemove(
		function(e) {
			// Get the data stored to the div.image-magnify image.
			var data = $(this).parent().data();

			// Get the position of the thumbnail to determine the position of the mouse.
			var offset = $(this).offset();

			// Re-calculate the ratio of popup to thumbnail.
			var ratio = {
					height: popup.height / thumbnail.height,
					width: popup.width / thumbnail.width
			};

			// Get the X and Y coordinations of the mouse in relation to the thumbnail.
			var x = e.pageX - offset.left;
			var y = e.pageY - offset.top;
			
			// Don't scroll beyond the thumbnail.
			// Used to prevent scrolling when the mouse is on the border.
			if(x < 0) {
				x = 0;
				$(this).children("div.popup").css("display", "none");
			}
			if(x > thumbnail.width) {
				x = thumbnail.width;
				$(this).children("div.popup").css("display", "none");
			}
			if (y < 0) {
				y = 0;
				$(this).children("div.popup").css("display", "none");
			}
			if(y > thumbnail.height) {
				y = thumbnail.height;
				$(this).children("div.popup").css("display", "none");
			}

			// Shift the background image of the popup
			$(this).children("div.popup").css("background-position", (x > 0 ? "-" + (x * (ratio.width - 1)) + "px" : 0) + " " + (y > 0 ? "-" + (y * (ratio.height - 1)) + "px" : 0));
		}
	);
}

function dimensiona_img_magnifier ( idImage , w , h , maxw , maxh )
{
	f = $("#" + idImage);

	rapp = h / w;
	w = parseInt(w);
	h = parseInt(h);
	if( h <= maxh && w <= maxw )
	{
		largh = w;
		altez = h;
		video = 1;		
	}
	else
	{
		largh = maxw;
		altez = largh * rapp;
		video = w / largh;

		if( altez > maxh ) 
		{
			altez = maxh;
			largh = altez / rapp;
			video = w / largh;
		}
		
	}

	f.width(largh);
	f.height(altez);
	
	magnifyImage(
		{	src:f.attr("src"), 	width:largh*video	,	height:altez*video 	},
		{	src:f.attr("src"), 	width:largh			,   height:altez 		}  // dim finestra foto
	);
	
}

//VERSIONE IMAGE MAGNIFIER PER GESTIONE IMMAGINI MULTIPLE SU SINGOLA PAGINA (non utilizzare il CSS image_magnifier.css)
function magnifyImagePlus( popup, thumbnail, id )	{
	if(id==null || id=="1")	id="";
	
	var ratio = {
		height: popup.height / thumbnail.height,
		width: popup.width / thumbnail.width
	};
	var thumb_url = thumbnail.src.replace(/\'/g, "\\\'");
	var popup_url = popup.src.replace(/\'/g, "\\\'")
	
	var thumbStyle = {
		"background-image" : "url('" + thumb_url + "')",
		"height" : thumbnail.height,
		"width" : thumbnail.width,
		"cursor": "move"
	}
	var popupStyle = {
		"background-image" : "url('" + popup_url + "')",
		"height" : thumbnail.height,
		"width" : thumbnail.width,
		"display": "none"
	}
	var image_magnifyStyle = {
		"cursor": "move",
		"display": "none",
		"background-repeat": "no-repeat"
	}

	$(".image_magnify"+id+"").css(image_magnifyStyle);
	$(".popup"+id+"").css(popupStyle);
	$(".thumbnail"+id+"").css(thumbStyle);
	
	$("div.image-magnify"+id+":last div.thumbnail"+id+"").mouseenter(
		function(e) {
			$(this).children("div.popup"+id+"").css("display", "block");
			$("#thumbnail_image"+id+"").hide();
		}
	)
	.mouseleave(
		function() {
			$(this).children("div.popup"+id+"").css("display", "none");
			$("#thumbnail_image"+id+"").show();
		}
	)
	.mousemove(
		function(e) {
			// Get the data stored to the div.image-magnify image.
			var data = $(this).parent().data();

			// Get the position of the thumbnail to determine the position of the mouse.
			var offset = $(this).offset();

			// Re-calculate the ratio of popup to thumbnail.
			var ratio = {
					height: popup.height / thumbnail.height,
					width: popup.width / thumbnail.width
			};

			// Get the X and Y coordinations of the mouse in relation to the thumbnail.
			var x = e.pageX - offset.left;
			var y = e.pageY - offset.top;
			
			// Don't scroll beyond the thumbnail.
			// Used to prevent scrolling when the mouse is on the border.
			if(x < 0) {
				x = 0;
				$(this).children("div.popup"+id+"").css("display", "none");
			}
			if(x > thumbnail.width) {
				x = thumbnail.width;
				$(this).children("div.popup"+id+"").css("display", "none");
			}
			if (y < 0) {
				y = 0;
				$(this).children("div.popup"+id+"").css("display", "none");
			}
			if(y > thumbnail.height) {
				y = thumbnail.height;
				$(this).children("div.popup"+id+"").css("display", "none");
			}

			// Shift the background image of the popup
			$(this).children("div.popup"+id+"").css("background-position", (x > 0 ? "-" + (x * (ratio.width - 1)) + "px" : 0) + " " + (y > 0 ? "-" + (y * (ratio.height - 1)) + "px" : 0));
		}
	);
}

function dimensiona_magnify ( id , w , h , maxw , maxh )
{
	id_name = "thumbnail_image" + id;

	f = $("#"+id_name+"");
	
	rapp = h / w;
	w = parseInt(w);
	h = parseInt(h);
	if( h <= maxh && w <= maxw )
	{
		largh = w;
		altez = h;
		video = 1;		
	}
	else
	{
		largh = maxw;
		altez = largh * rapp;
		video = w / largh;

		if( altez > maxh ) 
		{
			altez = maxh;
			largh = altez / rapp;
			video = w / largh;
		}
		
	}

	f.width(largh);
	f.height(altez);
	
	magnifyImagePlus(
		{	src:f.attr("src"), 	width:largh*video	,	height:altez*video 	},
		{	src:f.attr("src"), 	width:largh			,   height:altez 		},// dim finestra foto
			id
				);
	
}