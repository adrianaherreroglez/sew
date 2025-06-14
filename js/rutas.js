class Rutas {
    constructor() {
        this.apiFile = !!(window.File && window.FileReader && window.FileList && window.Blob);
        this.$mensajeError = $("<p>");
        this.maps = [];
        this.mapCounter = 0; // contador para IDs únicos
        this.inicializar();
    }

    inicializar() {
        var inputFile = document.querySelector("input[type='file']");
        inputFile.addEventListener("change", this.handleChange.bind(this));
    }

    mostrarError(mensaje) {
        this.$mensajeError.text(mensaje);
        if (!this.$mensajeError.parent().length) {
            $("main").prepend(this.$mensajeError);
        }
    }

    ocultarError() {
        if (this.$mensajeError.parent().length) {
            this.$mensajeError.remove();
        }
    }

    handleChange(e) {
        this.printInfo(e.target.files);
    }

    printInfo(files) {
        this.ocultarError();

        if (!this.apiFile) {
            this.mostrarError("No se ha podido leer el archivo pues su navegador no dispone de API File");
            return;
        }

        if (files.length === 0) {
            this.mostrarError("No ha seleccionado ningún archivo XML");
            return;
        }

        var reader = new FileReader();
        reader.onload = this.handleFileLoad.bind(this);
        reader.readAsText(files[0]);
    }

    handleFileLoad(e) {
        this.parseXML(e.target.result);
    }

    parseXML(xmlStr) {
        var xmlDoc = new DOMParser().parseFromString(xmlStr, "text/xml");

        if (xmlDoc.getElementsByTagName("parsererror").length > 0) {
            this.mostrarError("Error al parsear el XML");
            return;
        }

        var main = document.querySelector("main");
        // Oculta etiquetas y limpia contenido anterior
        var etiquetas = main.querySelectorAll("label");
        for (var i = 0; i < etiquetas.length; i++) etiquetas[i].style.display = "none";
        var hijos = main.querySelectorAll("section, h4, ul, p");
        for (var i = 0; i < hijos.length; i++) hijos[i].remove();

        var rutas = xmlDoc.getElementsByTagName("ruta");
        if (rutas.length === 0) {
            this.mostrarError("No se encontraron rutas en el XML");
            return;
        }

        for (var i = 0; i < rutas.length; i++) {
            this.crearSeccionRuta(rutas[i], i);
        }
    }

    crearSeccionRuta(ruta, index) {
        var seccion = document.createElement("section");
        var h3 = document.createElement("h3");
        h3.textContent = this.getText(ruta, "nombre");
        seccion.appendChild(h3);

        var campos = ["tipo","transporte","fechaInicio","horaInicio","duracion","agencia","descripcion","personasAdecuadas","lugarInicio","direccionInicio"];
        for (var j = 0; j < campos.length; j++) {
            var p = document.createElement("p");
            if (campos[j].length > 0) {
                var label = document.createElement("label");
                label.textContent = campos[j].charAt(0).toUpperCase() + campos[j].slice(1) + ": ";
                p.appendChild(label);
            }
            p.appendChild(document.createTextNode(this.getText(ruta, campos[j])));
            seccion.appendChild(p);
        }

        this.procesarCoordenadas(ruta, seccion);
        this.procesarReferencias(ruta, seccion);
        this.procesarHitos(ruta, seccion);
        this.procesarKML(ruta, seccion);
        this.procesarSVG(index, seccion);

        document.querySelector("main").appendChild(seccion);
    }

    procesarCoordenadas(ruta, sec) {
        var coord = ruta.getElementsByTagName("coordenadasInicio")[0];
        if (coord) {
            var lon = this.getText(coord, "longitud");
            var lat = this.getText(coord, "latitud");
            var alt = this.getText(coord, "altitud");
            var uni = coord.getElementsByTagName("altitud")[0] && coord.getElementsByTagName("altitud")[0].getAttribute("unidades") || "";
            var p = document.createElement("p");
            var label = document.createElement("label");
            label.textContent = "Coordenadas geográficas de inicio de la ruta: ";
            p.appendChild(label);
            p.appendChild(document.createTextNode("Longitud: " + lon + ", Latitud: " + lat + ", Altitud: " + alt + " " + uni));
            sec.appendChild(p);
        }
    }

    procesarReferencias(ruta, sec) {
        var refs = ruta.getElementsByTagName("referencia");
        if (refs.length > 0) {
            var h4 = document.createElement("h4");
            h4.textContent = "Referencias";
            sec.appendChild(h4);
            var ul = document.createElement("ul");
            for (var i = 0; i < refs.length; i++) {
                var li = document.createElement("li");
                var a = document.createElement("a");
                var texto = refs[i].textContent.trim();
                a.href = texto;
                a.textContent = texto;
                li.appendChild(a);
                ul.appendChild(li);
            }
            sec.appendChild(ul);
        }
    }

    procesarHitos(ruta, sec) {
        var hitos = ruta.getElementsByTagName("hito");
        if (hitos.length > 0) {
            var h4 = document.createElement("h4");
            h4.textContent = "Hitos";
            sec.appendChild(h4);
            for (var i = 0; i < hitos.length; i++) {
                this.crearHito(hitos[i], sec);
            }
        }
    }

    crearHito(hito, sec) {
        var s = document.createElement("section");
        var h5 = document.createElement("h5");
        h5.textContent = this.getText(hito, "nombre");
        s.appendChild(h5);

        var p = document.createElement("p");
        var label = document.createElement("label");
        label.textContent = "Descripción: ";
        p.appendChild(label);
        p.appendChild(document.createTextNode(this.getText(hito, "descripcion")));
        s.appendChild(p);

        var gal = hito.getElementsByTagName("galeriaFotografias")[0];
        if (gal) {
            var foto = gal.getElementsByTagName("foto")[0];
            if (foto && foto.textContent.trim()) {
                var img = document.createElement("img");
                img.src = foto.textContent.trim();
                img.alt = this.getText(hito, "nombre");
                s.appendChild(img);
            }
        }

        var dist = hito.getElementsByTagName("distancia")[0];
        if (dist) {
            var pDist = document.createElement("p");
            var labelDist = document.createElement("label");
            labelDist.textContent = "Distancia: ";
            pDist.appendChild(labelDist);
            pDist.appendChild(document.createTextNode(dist.textContent.trim() + " " + (dist.getAttribute("unidad") || "")));
            s.appendChild(pDist);
        }

        sec.appendChild(s);
    }

    procesarKML(ruta, sec) {
        var kml = this.getText(ruta, "kml");
        if (kml) {
            var cont = document.createElement("section");
            var h4 = document.createElement("h4");
            h4.textContent = "Planimetría de la ruta";
            cont.appendChild(h4);
            this.fetchKML(kml, cont);
            sec.appendChild(cont);
        }
    }

    fetchKML(path, cont) {
        fetch(path)
            .then(this.kmlResponseHandler.bind(this, cont))
            .catch(this.kmlErrorHandler.bind(this, cont));
    }

    kmlResponseHandler(cont, response) {
        if (!response.ok) {
            this.handleKMLError(cont);
            return;
        }
        response.text().then(this.renderMap.bind(this, cont));
    }

    kmlErrorHandler(cont, error) {
        this.handleKMLError(cont);
    }

    handleKMLError(cont) {
        var p = document.createElement("p");
        p.textContent = "Error al cargar el KML.";
        cont.appendChild(p);
    }

    getZoomByWidth(width) {
        if (width <= 480) return 16;
        if (width <= 768) return 14;
        return 13;
    }

    renderMap(cont, kmlText) {
        var parser = new DOMParser();
        var kmlDoc = parser.parseFromString(kmlText, "text/xml");

        var features = new ol.format.KML().readFeatures(kmlDoc, {
            dataProjection: 'EPSG:4326',
            featureProjection: 'EPSG:3857'
        });

        var vectorSource = new ol.source.Vector({
            features: features
        });

        var vectorLayer = new ol.layer.Vector({
            source: vectorSource
        });

        var mapContainer = document.createElement("figure");
        mapContainer.id = "mapaRuta_" + this.mapCounter;
        cont.appendChild(mapContainer);

        var initialZoom = this.getZoomByWidth(window.innerWidth);

        var map = new ol.Map({
            target: mapContainer,
            layers: [
                new ol.layer.Tile({ source: new ol.source.OSM() }),
                vectorLayer
            ],
            view: new ol.View({
                center: features.length
                    ? features[0].getGeometry().getCoordinates()
                    : ol.proj.fromLonLat([-5.7, 43.2]),
                zoom: initialZoom
            }),
            controls: []
        });

        var extent = vectorSource.getExtent();
        if (!ol.extent.isEmpty(extent)) {
            map.getView().fit(extent, { padding: [20, 20, 20, 20] });
        }

        this.maps.push(map);
        this.mapCounter++;

        if (this.maps.length === 1) {
            window.addEventListener('resize', this.onResizeHandler.bind(this));
        }
    }

    onResizeHandler() {
        var newZoom = this.getZoomByWidth(window.innerWidth);
        for (var i = 0; i < this.maps.length; i++) {
            this.maps[i].getView().setZoom(newZoom);
            this.maps[i].updateSize();
        }
    }

    procesarSVG(index, sec) {
        var svgPath = "xml/altimetria" + (index + 1) + ".svg";
        this.fetchSVG(svgPath, sec);
    }

    fetchSVG(path, cont) {
        fetch(path)
            .then(this.svgResponseHandler.bind(this, cont))
            .catch(this.svgErrorHandler.bind(this, cont));
    }

    svgResponseHandler(cont, response) {
        if (!response.ok) {
            this.handleSVGError(cont);
            return;
        }
        response.text().then(this.renderSVG.bind(this, cont));
    }

    svgErrorHandler(cont, error) {
        this.handleSVGError(cont);
    }

    handleSVGError(cont) {
        var p = document.createElement("p");
        p.textContent = "Error al cargar el archivo SVG.";
        cont.appendChild(p);
    }

    renderSVG(cont, svgText) {
        var svgDoc = new DOMParser().parseFromString(svgText, "image/svg+xml").documentElement;
        svgDoc.removeAttribute("width");
        svgDoc.removeAttribute("height");

        var section = document.createElement("section");
        var titulo = document.createElement("h4");
        titulo.textContent = "Altimetría de la ruta";
        var figure = document.createElement("figure");

        figure.appendChild(svgDoc);
        section.appendChild(titulo);
        section.appendChild(figure);

        cont.appendChild(section);
    }

    getText(el, tag) {
        var t = el.getElementsByTagName(tag)[0];
        return t ? t.textContent.trim() : "";
    }
}

new Rutas();

