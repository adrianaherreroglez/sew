class Rutas {
    constructor() {
        this.apiFile = Boolean(window.File && window.FileReader && window.FileList && window.Blob);
        this.$mensajeError = $("<p>").css({ color: "red", fontWeight: "bold" }).hide();
        $("main").prepend(this.$mensajeError);
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

        const file = files[0];
        const reader = new FileReader();

        reader.onload = this.onFileLoad.bind(this);
        reader.readAsText(file);
    }

    onFileLoad(e) {
        const xmlStr = e.target.result;
        this.parseXML(xmlStr);
    }

    parseXML(xmlStr) {
        const xmlDoc = new DOMParser().parseFromString(xmlStr, "text/xml");

        if (xmlDoc.getElementsByTagName("parsererror").length > 0) {
            this.$mensajeError.text("Error al parsear el XML").show();
            return;
        }

        const main = $("main");
        main.find("label").hide();
        main.children("section, h4, ul, p").remove();

        const rutas = xmlDoc.getElementsByTagName("ruta");

        if (rutas.length === 0) {
            this.$mensajeError.text("No se encontraron rutas en el XML").show();
            return;
        }

        for (let i = 0; i < rutas.length; i++) {
            const ruta = rutas[i];
            const seccion = $("<section></section>");
            const nombre = this.getTextContent(ruta, "nombre");
            seccion.append(`<h3>${nombre}</h3>`);

            this.appendP(seccion, "Tipo", this.getTextContent(ruta, "tipo"));
            this.appendP(seccion, "Transporte", this.getTextContent(ruta, "transporte"));
            this.appendP(seccion, "Fecha Inicio", this.getTextContent(ruta, "fechaInicio"));
            this.appendP(seccion, "Hora Inicio", this.getTextContent(ruta, "horaInicio"));
            this.appendP(seccion, "Duración", this.getTextContent(ruta, "duracion"));
            this.appendP(seccion, "Agencia", this.getTextContent(ruta, "agencia"));
            this.appendP(seccion, "Descripción", this.getTextContent(ruta, "descripcion"));
            this.appendP(seccion, "Personas adecuadas", this.getTextContent(ruta, "personasAdecuadas"));
            this.appendP(seccion, "Lugar de inicio", this.getTextContent(ruta, "lugarInicio"));
            this.appendP(seccion, "Dirección de inicio", this.getTextContent(ruta, "direccionInicio"));

            const coordenadas = ruta.getElementsByTagName("coordenadasInicio")[0];
            if (coordenadas) {
                const longitud = this.getTextContent(coordenadas, "longitud");
                const latitud = this.getTextContent(coordenadas, "latitud");
                const altitud = this.getTextContent(coordenadas, "altitud");
                const unidades = coordenadas.getElementsByTagName("altitud")[0]?.getAttribute("unidades") || "";
                const textoCoord = `Longitud: ${longitud}, Latitud: ${latitud}, Altitud: ${altitud} ${unidades}`;
                this.appendP(seccion, "Coordenadas geográficas de inicio de la ruta", textoCoord);
            }

            const referencias = ruta.getElementsByTagName("referencia");
            if (referencias.length > 0) {
                const ul = $("<ul></ul>");
                for (let j = 0; j < referencias.length; j++) {
                    const ref = referencias[j].textContent.trim();
                    ul.append(`<li><a href='${ref}'>${ref}</a></li>`);
                }
                seccion.append("<h4>Referencias</h4>").append(ul);
            }

            const recomendacion = this.getTextContent(ruta, "recomendacion");
            if (recomendacion) {
                this.appendP(seccion, "Recomendación", recomendacion);
            }

            const hitos = ruta.getElementsByTagName("hito");
            if (hitos.length > 0) {
                seccion.append("<h4>Hitos</h4>");
                for (let hito of hitos) {
                    const shito = $("<section></section>");
                    shito.append(`<h5>${this.getTextContent(hito, "nombre")}</h5>`);
                    this.appendP(shito, "Descripción", this.getTextContent(hito, "descripcion"));

                    const galeria = hito.getElementsByTagName("galeriaFotografias")[0];
                    if (galeria) {
                        const foto = galeria.getElementsByTagName("foto")[0];
                        if (foto && foto.textContent.trim() !== "") {
                            const src = foto.textContent.trim();
                            const img = $("<img>").attr("src", src).attr("alt", this.getTextContent(hito, "nombre"));
                            shito.append(img);
                        }
                    }

                    const distancia = hito.getElementsByTagName("distancia")[0];
                    if (distancia) {
                        const dTexto = distancia.textContent.trim();
                        const unidad = distancia.getAttribute("unidad") || "";
                        this.appendP(shito, "Distancia", `${dTexto} ${unidad}`);
                    }
                    seccion.append(shito);
                }
            }

            // Carga del KML
            const kml = this.getTextContent(ruta, "kml");
            if (kml) {
                const kmlSec = $("<section></section>");
                kmlSec.append("<h3>Planimetría de la ruta</h3>");
                this.loadAndShowKML(kml, kmlSec);
                seccion.append(kmlSec);
            }

            // Carga del SVG
            const svgPath = `xml/altimetria${i + 1}.svg`;
            const svgSec = $("<section></section>");
            svgSec.append("<h3>Altimetría de la ruta</h3>");
            this.loadAndShowSVG(svgPath, svgSec);
            seccion.append(svgSec);

            main.append(seccion);
        }
    }

    appendP(section, label, content) {
        const p = $("<p></p>");
        if (label) p.append($("<label></label>").text(`${label}: `));
        p.append(document.createTextNode(content));
        section.append(p);
    }

    getTextContent(element, tag) {
        const el = element.getElementsByTagName(tag)[0];
        return el ? el.textContent.trim() : "";
    }

    loadAndShowKML(kmlPath, container) {
        fetch(kmlPath)
            .then(response => {
                if (!response.ok) throw new Error("No se pudo cargar el archivo KML");
                return response.text();
            })
            .then(kmlText => this.showMap(kmlText, container))
            .catch(() => this.appendP(container, "", "Error al cargar el KML."));
    }

    showMap(kmlText, container) {
  const parser = new DOMParser();
  const kmlDoc = parser.parseFromString(kmlText, "text/xml");
  const features = [];

  const lineCoords = kmlDoc.querySelector("LineString > coordinates");
  if (lineCoords) {
    const coordsArray = lineCoords.textContent.trim().split(/\s+/).map(c => {
      const [lon, lat] = c.split(",").map(Number);
      return ol.proj.fromLonLat([lon, lat]);
    });

    const line = new ol.Feature(new ol.geom.LineString(coordsArray));
    line.setStyle(new ol.style.Style({
      stroke: new ol.style.Stroke({ color: "red", width: 3 }),
    }));
    features.push(line);
  }

  const points = kmlDoc.querySelectorAll("Placemark > Point > coordinates");
  for (let point of points) {
    const [lon, lat] = point.textContent.trim().split(",").map(Number);
    const coord = ol.proj.fromLonLat([lon, lat]);
    const feature = new ol.Feature(new ol.geom.Point(coord));
    feature.setStyle(new ol.style.Style({
      image: new ol.style.Icon({
        src: "multimedia/imagenes/marcador.png",
        scale: 0.05,
        anchor: [0.5, 1],
      }),
    }));
    features.push(feature);
  }

  const vectorLayer = new ol.layer.Vector({
    source: new ol.source.Vector({ features }),
  });

  // Creamos un figure para el mapa con id único
  const figureMap = document.createElement("figure");
  figureMap.id = "mapa-" + Date.now();

  // Aplicamos estilos al figure para que OpenLayers pueda renderizar bien
  Object.assign(figureMap.style, {
    width: "100%",
    height: "400px",
    border: "1px solid #ccc",
    borderRadius: "10px",
    boxShadow: "0 2px 8px rgba(0,0,0,0.2)",
    margin: "20px auto",
  });

  // Añadimos el figure al contenedor padre
  container.append(figureMap);

  // Inicializamos el mapa apuntando al figure directamente (sin div interno)
  const map = new ol.Map({
    target: figureMap,
    layers: [
      new ol.layer.Tile({ source: new ol.source.OSM() }),
      vectorLayer
    ],
    view: new ol.View({
      center: features.length > 0 ? features[0].getGeometry().getCoordinates() : ol.proj.fromLonLat([-5.7, 43.2]),
      zoom: 13,
    }),
    controls: []
  });

  const extent = vectorLayer.getSource().getExtent();
  map.getView().fit(extent, { padding: [20, 20, 20, 20] });
}


    loadAndShowSVG(svgPath, container) {
        fetch(svgPath)
            .then(response => {
                if (!response.ok) throw new Error("No se pudo cargar el archivo SVG");
                return response.text();
            })
            .then(svgText => {
                const parser = new DOMParser();
                const svgDoc = parser.parseFromString(svgText, "image/svg+xml").documentElement;
                svgDoc.removeAttribute("width");
                svgDoc.removeAttribute("height");
                const serializedSVG = new XMLSerializer().serializeToString(svgDoc);
                const figure = $("<figure></figure>").html(serializedSVG);
                container.append(figure);
            })
            .catch(() => container.append("<p>Error al cargar el archivo SVG.</p>"));
    }
}

const rutas = new Rutas();
