class Rutas {
    constructor() {
        this.apiFile = !!(window.File && window.FileReader && window.FileList && window.Blob);
        this.$mensajeError = $("<p>").css({ color: "red", fontWeight: "bold" }).hide();
        $("main").prepend(this.$mensajeError);
        this.inicializar();
    }

    inicializar() {
    
    const inputFile = document.querySelector("input[type='file']");
    $(inputFile).on("change", this.handleChange.bind(this));
}


    handleChange(e) {
        this.printInfo(e.target.files);
    }

    printInfo(files) {
        this.$mensajeError.hide();

        if (!this.apiFile) {
            $("main").append("<h4>No se ha podido leer el archivo pues su navegador no dispone de API File</h4>");
            return;
        }

        if (files.length === 0) {
            this.$mensajeError.text('No ha seleccionado ningún archivo XML').show();
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
            this.$mensajeError.text("Error al parsear el XML").show();
            return;
        }

        var main = $("main");
        main.find("label").hide();
        main.children("section, h4, ul, p").remove();

        var rutas = xmlDoc.getElementsByTagName("ruta");
        if (rutas.length === 0) {
            this.$mensajeError.text("No se encontraron rutas en el XML").show();
            return;
        }

        for (var i = 0; i < rutas.length; i++) {
            this.crearSeccionRuta(rutas[i], i);
        }
    }

    crearSeccionRuta(ruta, index) {
        var seccion = $("<section></section>");
        seccion.append("<h3>" + this.getText(ruta, "nombre") + "</h3>");

        var campos = ["tipo","transporte","fechaInicio","horaInicio","duracion","agencia","descripcion","personasAdecuadas","lugarInicio","direccionInicio"];
        for (var j = 0; j < campos.length; j++) {
            var etiqueta = campos[j];
            this.appendP(seccion, etiqueta.charAt(0).toUpperCase() + etiqueta.slice(1), this.getText(ruta, etiqueta));
        }

        this.procesarCoordenadas(ruta, seccion);
        this.procesarReferencias(ruta, seccion);
        this.procesarHitos(ruta, seccion);
        this.procesarKML(ruta, seccion);
        this.procesarSVG(index, seccion);

        $("main").append(seccion);
    }

    procesarCoordenadas(ruta, sec) {
        var coord = ruta.getElementsByTagName("coordenadasInicio")[0];
        if (coord) {
            var lon = this.getText(coord, "longitud");
            var lat = this.getText(coord, "latitud");
            var alt = this.getText(coord, "altitud");
            var uni = coord.getElementsByTagName("altitud")[0]?.getAttribute("unidades") || "";
            this.appendP(sec, "Coordenadas geográficas de inicio de la ruta", "Longitud: " + lon + ", Latitud: " + lat + ", Altitud: " + alt + " " + uni);
        }
    }

    procesarReferencias(ruta, sec) {
        var refs = ruta.getElementsByTagName("referencia");
        if (refs.length > 0) {
            var ul = $("<ul></ul>");
            for (var i = 0; i < refs.length; i++) {
                var texto = refs[i].textContent.trim();
                ul.append("<li><a href='" + texto + "'>" + texto + "</a></li>");
            }
            sec.append("<h4>Referencias</h4>").append(ul);
        }
    }

    procesarHitos(ruta, sec) {
        var hitos = ruta.getElementsByTagName("hito");
        if (hitos.length > 0) {
            sec.append("<h4>Hitos</h4>");
            for (var i = 0; i < hitos.length; i++) {
                this.crearHito(hitos[i], sec);
            }
        }
    }

    crearHito(hito, sec) {
        var s = $("<section></section>");
        s.append("<h5>" + this.getText(hito, "nombre") + "</h5>");
        this.appendP(s, "Descripción", this.getText(hito, "descripcion"));

        var gal = hito.getElementsByTagName("galeriaFotografias")[0];
        if (gal) {
            var foto = gal.getElementsByTagName("foto")[0];
            if (foto && foto.textContent.trim()) {
                s.append("<img src='" + foto.textContent.trim() + "' alt='" + this.getText(hito,"nombre") + "'>");
            }
        }

        var dist = hito.getElementsByTagName("distancia")[0];
        if (dist) {
            this.appendP(s, "Distancia", dist.textContent.trim() + " " + (dist.getAttribute("unidad") || ""));
        }

        sec.append(s);
    }

    procesarKML(ruta, sec) {
        var kml = this.getText(ruta, "kml");
        if (kml) {
            var cont = $("<section></section>").append("<h4>Planimetría de la ruta</h4>");
            this.fetchKML(kml, cont);
            sec.append(cont);
        }
    }

    fetchKML(path, cont) {
        fetch(path)
            .then(this.handleKMLResponse.bind(this, cont))
            .catch(this.handleKMLError.bind(this, cont));
    }

    handleKMLResponse(cont, response) {
        if (!response.ok) { this.handleKMLError(cont); return; }
        response.text().then(this.renderMap.bind(this, cont));
    }

    handleKMLError(cont) {
        this.appendP(cont, "", "Error al cargar el KML.");
    }

    renderMap(cont, kmlText) {
    const parser = new DOMParser();
    const kmlDoc = parser.parseFromString(kmlText, "text/xml");
    const features = [];

    // 1. Crear línea de ruta
    const lineCoords = kmlDoc.querySelector("LineString > coordinates");
    if (lineCoords) {
        const coordsText = lineCoords.textContent.trim().split(/\s+/);
        const coordsArray = coordsText.map(function (c) {
            const parts = c.split(",").map(Number);
            return ol.proj.fromLonLat([parts[0], parts[1]]);
        });

        const line = new ol.Feature(new ol.geom.LineString(coordsArray));
        line.setStyle(new ol.style.Style({
            stroke: new ol.style.Stroke({ color: "red", width: 3 })
        }));
        features.push(line);
    }

    // 2. Añadir puntos (opcional, si hay <Point>)
    const puntos = kmlDoc.querySelectorAll("Placemark > Point > coordinates");
    for (let i = 0; i < puntos.length; i++) {
        const parts = puntos[i].textContent.trim().split(",").map(Number);
        const coord = ol.proj.fromLonLat([parts[0], parts[1]]);
        const marker = new ol.Feature(new ol.geom.Point(coord));
        marker.setStyle(new ol.style.Style({
            image: new ol.style.Icon({
                src: "multimedia/imagenes/marcador.png",
                scale: 0.05,
                anchor: [0.5, 1]
            })
        }));
        features.push(marker);
    }

    // 3. Crear contenedor del mapa
    const mapContainer = document.createElement("figure");
    mapContainer.style.width = "100%";
    mapContainer.style.height = "400px";
    mapContainer.style.margin = "20px auto";
    mapContainer.style.border = "1px solid #ccc";
    mapContainer.style.borderRadius = "10px";
    mapContainer.style.boxShadow = "0 2px 8px rgba(0,0,0,0.2)";
    cont.append(mapContainer); // añadir al section

    // 4. Crear capa vectorial
    const vectorLayer = new ol.layer.Vector({
        source: new ol.source.Vector({ features })
    });

    // 5. Instanciar el mapa
    const map = new ol.Map({
        target: mapContainer,
        layers: [
            new ol.layer.Tile({ source: new ol.source.OSM() }),
            vectorLayer
        ],
        view: new ol.View({
            center: features.length
                ? features[0].getGeometry().getCoordinates()
                : ol.proj.fromLonLat([-5.7, 43.2]), // centro por defecto si no hay features
            zoom: 13
        }),
        controls: []
    });

    // 6. Ajustar vista
    const extent = vectorLayer.getSource().getExtent();
    if (!ol.extent.isEmpty(extent)) {
        map.getView().fit(extent, { padding: [20, 20, 20, 20] });
    }
}


    procesarSVG(index, sec) {
        var svgPath = "xml/altimetria" + (index + 1) + ".svg";
        this.fetchSVG(svgPath, sec);
    }

    fetchSVG(path, cont) {
        fetch(path)
            .then(this.handleSVGResponse.bind(this, cont))
            .catch(this.handleSVGError.bind(this, cont));
    }

    handleSVGResponse(cont, response) {
        if (!response.ok) { this.handleSVGError(cont); return; }
        response.text().then(this.renderSVG.bind(this, cont));
    }

    handleSVGError(cont) {
        cont.append("<p>Error al cargar el archivo SVG.</p>");
    }

    renderSVG(cont, svgText) {
    const svgDoc = new DOMParser().parseFromString(svgText, "image/svg+xml").documentElement;
    svgDoc.removeAttribute("width");
    svgDoc.removeAttribute("height");

    const figure = $("<figure></figure>").css({
        width: "100%",
        margin: "20px auto",
        border: "1px solid #ccc",
        borderRadius: "10px",
        boxShadow: "0 2px 8px rgba(0,0,0,0.2)",
        padding: "10px"
    });

    // Añadir título dentro del figure
    const caption = $("<figcaption>Altimetría de la ruta</figcaption>").css({
        fontWeight: "bold",
        textAlign: "center",
        marginBottom: "10px"
    });

    figure.append(caption);
    figure.append(new XMLSerializer().serializeToString(svgDoc));

    cont.append(figure);
}


    appendP(sec, label, text) {
        var p = $("<p></p>");
        if (label) { p.append($("<label></label>").text(label + ": ")); }
        p.append(document.createTextNode(text));
        sec.append(p);
    }

    getText(el, tag) {
        var t = el.getElementsByTagName(tag)[0];
        return t ? t.textContent.trim() : "";
    }
}

// Sólo se instancia dentro de la clase principal
new Rutas();
