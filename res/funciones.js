function en_carga () {
	// En vez de mostrar la pestaña A al abrir la pagina, abrimos la pestaña de busqueda, que siempre estara
	//if ( document.getElementById("A") ) {
		//openTab ( null, "A");
		$("#b_busqueda").click();
	//}
}


// TODO: Cambiar a jQuery para simplificar codigo
// TODO: Ya que usamos jQuery, tal vez deberiamos pestañas de jQueryUI
// https://www.w3schools.com/howto/howto_js_tabs.asp modificado...
function openTab(evt, tabName) {
    //console.debug('openTab('+evt+', '+tabName+')');

	// Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    $('.tabcontent').hide();

    // Get all elements with class="tablinks" and remove the class "active"
    $('.tablinks').removeClass('active');

    // Show the current tab, and add an "active" class to the button that opened the tab
    $('#'+tabName).css( "display", "block" );
    
    if ( evt ) {
    	if ( evt.currentTarget ) {
    		evt.currentTarget.className += " active";
    	} else {
    		var _class = $('#'+tabName).attr('class');
    		//console.log('tabName.class:'+_class);
    		$('#'+tabName).attr('class', _class + " active" );
    	}
    } else {
    	//document.getElementById('b_'+tabName).className += " active";
        $('#'+tabName).addClass('active');
    }
    
}

// TODO: Cambiar a jQuery para simplificar codigo
function busqueda_xhr ( consulta, tabla_destino )
{
	limpiar_resultado ( tabla_destino );
	
	var request = new XMLHttpRequest();
	
	request.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			cFunction(this);
		}
	};
	var url = location.href.split('?')[0]+'?xhr=true&consulta='+consulta;
	//alert ( url );
	request.open("GET", url, true);
	request.onreadystatechange = function() {
		var done = 4, ok = 200;
		if (request.readyState == done && request.status == ok) {
			if (request.responseText) {
				// TODO: Comprobar la existencia de resultados
				
				var resultado = request.responseText.replace(/(^"|\$"$|"$)/g, '').split ( '$' );
				//alert(resultado[0]);
				var destino = document.getElementById(tabla_destino);
				destino.style.display = '';
				var contacto;
				var l_resultado = resultado.length;
				// Añadimos los contactos al resultado
				for ( var i = 0; i < l_resultado ; i++ ) {
					var fila = destino.insertRow();
					var nombre = fila.insertCell(0);
					var extension = fila.insertCell(1);
					contacto = resultado[i].split ( ' => ' );
					//alert ( contacto[0] + '<->' + contacto[1] );
					nombre.innerText = contacto[0];
					extension.innerText = contacto[1];					
				}
				
				$('#cabecera_resultados').html(function(){
					//console.debug($(this).html());

					var texto_consulta = $(this).html();
					$(this).empty();
					texto_consulta = 'Resultados de la consulta "'+consulta+'" <em>(seg&uacute;n aparecen en los terminales)</em>';
					
					//console.debug(texto_consulta);

					return texto_consulta;
				});
			}
		}
	};
	request.send(null);
}

//TODO: Cambiar a jQuery para simplificar codigo
function limpiar_resultado ( tabla_destino )
{
	var destino = document.getElementById( tabla_destino );
	destino.style.display = 'none';
	
	// Borrar todas las filas de destino excepto la del titulo en destino.
	for ( var i = destino.rows.length - 1 ; i > 0 ; i-- ) {
		destino.deleteRow (-1);
	}
}


jQuery(function($) {
	$('.bookmark-this').click(function(e)
	{
		
	    var bookmarkURL = window.location.href;
	    var bookmarkTitle = document.title;
	    var bookmarklet_code = "javascript:(function(){var%20jsCode=document.createElement('script');jsCode.setAttribute('src','https://"+location.href.split('?')[0].split('://')[1]+"?script=true');document.body.appendChild(jsCode);}());";
	    
//	    if ( $(this).attr("href") == '?script=true') {
	    if ( $(this).hasClass("bookmarklet") ) {
	    	
	    	// TODO: Consultar mediante xhr la url de ?script=true
	    	bookmarkURL = bookmarklet_code;
	    	bookmarkTitle = "Teléfonos";
	    } else {
	    	// Sustituimos el '?' inicial del href del enlace para que se una a la url que ya tiene ?upd=true
	    	// TODO: No deberia llegarse a este punto sin ?upd=true, pero hay que asegurarse de alguna manera
		    bookmarkURL = "https://"+window.location.href.split('://')[1]+$(this).attr("href").replace(/^\?/, "&");
	    }
	
	    if ('addToHomescreen' in window && addToHomescreen.isCompatible) {
			// Mobile browsers
			addToHomescreen({
				autostart : false,
				startDelay : 0
			}).show(true);
		} else if (window.sidebar && window.sidebar.addPanel) {
			// Firefox <=22
	    	alert ('Desmarcar la casilla "Cargar este marcador en el panel lateral"');
			window.sidebar.addPanel(bookmarkTitle, bookmarkURL, '');
		} else if ((window.sidebar && /Firefox/i.test(navigator.userAgent))
				|| (window.opera && window.print)) {
			// Firefox 23+ and Opera <=14
	    	alert ('Desmarcar la casilla "Cargar este marcador en el panel lateral"');
			$(this).attr({
				href : bookmarkURL,
				title : bookmarkTitle,
				rel : 'sidebar'
			}).off(e);
			return true;
		} else if (window.external && ('AddFavorite' in window.external)) {
			// IE Favorites
			if ( $(this).hasClass("bookmarklet") ) {
				alert ( 'Arrastrar este enlace a la barra de marcadores' );
			} else {
				window.external.AddFavorite(bookmarkURL, bookmarkTitle);
			}
		} else {
			// Other browsers (mainly WebKit & Blink - Safari, Chrome, Opera 15+)
			alert('Presionar '
					+ (navigator.userAgent.toLowerCase().indexOf('mac') != -1 ? 'Command/Cmd'
							: 'Control')
					+ ' + D para añadir esta página a favoritos.');
		}	    
	    return false;
	});
});


/* https://www.w3schools.com/howto/howto_js_dropdown.asp */
/* When the user clicks on the button, toggle between hiding and showing the dropdown content */
function bookmark_dropdown() {
    $("#bookmark_dropdown").toggleClass("show");
}

// TODO: Registrar el click sobre el boton con jQuery?
// Close the dropdown menu if the user clicks outside of it
window.onclick = function(event) {
	if (event.target) {
		if (!event.target.matches('.dropbtn')) {
			$(".dropdown-content").removeClass("show");
		}
	}
}
