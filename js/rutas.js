class Rutas {
  constructor() {
    this.apiFile = !!(window.File && window.FileReader && window.FileList && window.Blob);
  }

  printInfo(files) {
    if (!this.apiFile) {
      $("main").append("<h4>No se ha podido leer el archivo pues su navegador no dispone de API File</h4>");
      return;
    }

    if (files.length === 0) {
      alert('No ha seleccionado ningún archivo XML');
      return;
    }

    const file = files[0];
    const reader = new FileReader();

    reader.onload = this.onFileLoad.bind(this);
    reader.readAsText(file);
  }

  onFileLoad(e) {
    var xmlStr = e.target.result;
    this.parseXML(xmlStr);
  }

  parseXML(xmlStr) {
    var xmlDoc = new DOMParser().parseFromString(xmlStr, "text/xml");

    if (xmlDoc.getElementsByTagName("parsererror").length > 0) {
      alert("Error al parsear el XML");
      return;
    }

    var $main = $("main");
    $main.find("label").hide();
    $main.children("section, h4, ul, p").remove();

    var rutas = xmlDoc.getElementsByTagName("ruta");

    if (rutas.length === 0) {
      alert("No se encontraron rutas en el XML");
      return;
    }

    for (var i = 0; i < rutas.length; i++) {
      var ruta = rutas[i];
      var $section = $("<section></section>");

      var nombre = this.getTextContent(ruta, "nombre");
      $section.append("<h3>" + nombre + "</h3>");
      this.appendP($section, "Tipo", this.getTextContent(ruta, "tipo"));
      this.appendP($section, "Transporte", this.getTextContent(ruta, "transporte"));
      this.appendP($section, "Fecha Inicio", this.getTextContent(ruta, "fechaInicio"));
      this.appendP($section, "Hora Inicio", this.getTextContent(ruta, "horaInicio"));
      this.appendP($section, "Duración", this.getTextContent(ruta, "duracion"));
      this.appendP($section, "Agencia", this.getTextContent(ruta, "agencia"));
      this.appendP($section, "Descripción", this.getTextContent(ruta, "descripcion"));
      this.appendP($section, "Personas adecuadas", this.getTextContent(ruta, "personasAdecuadas"));
      this.appendP($section, "Lugar de inicio", this.getTextContent(ruta, "lugarInicio"));
      this.appendP($section, "Dirección de inicio ", this.getTextContent(ruta, "direccionInicio"));

      var referencias = ruta.getElementsByTagName("referencia");
      if (referencias.length > 0) {
        var $ul = $("<ul></ul>");
        for (var j = 0; j < referencias.length; j++) {
          var url = referencias[j].textContent.trim();
          $ul.append("<li><a href='" + url + "'>" + url + "</a></li>");
        }
        $section.append("<h4>Referencias</h4>").append($ul);
      }

     var hitos = ruta.getElementsByTagName("hito");
if (hitos.length > 0) {
  $section.append("<h4>Hitos</h4>");
  for (var j = 0; j < hitos.length; j++) {
    var hito = hitos[j];
    var $hito = $("<section></section>");
    $hito.append("<h5>" + this.getTextContent(hito, "nombre") + "</h5>");
    this.appendP($hito, "Descripción", this.getTextContent(hito, "descripcion"));

    var galeria = hito.getElementsByTagName("galeriaFotografias")[0];
    if (galeria) {
      var fotoEl = galeria.getElementsByTagName("foto")[0];
      if (fotoEl && fotoEl.textContent.trim() !== "") {
        var foto = fotoEl.textContent.trim();
        var $img = $("<img>")
          .attr("src", foto)
          .attr("alt", this.getTextContent(hito, "nombre"));
        $hito.append($img);
      }
    }

    var distanciaEl = hito.getElementsByTagName("distancia")[0];
    if (distanciaEl) {
      var dist = distanciaEl.textContent.trim();
      var unidad = distanciaEl.getAttribute("unidad") || "";
      this.appendP($hito, "Distancia", dist + " " + unidad);
    }
    $section.append($hito);
  }
}


      // Planimetría (KML)
      var kml = this.getTextContent(ruta, "kml");
      if (kml) {
        var $planimetriaSection = $("<section></section>");
        $planimetriaSection.append("<h3>Planimetría de la ruta</h3>");
        this.loadAndShowKML(kml, $planimetriaSection);
        $section.append($planimetriaSection);
      }

      // Altimetría (SVG)
      var svgPath = "xml/altimetria" + (i + 1) + ".svg";
      var $altimetriaSection = $("<section></section>");
      $altimetriaSection.append("<h3>Altimetría de la ruta</h3>");
      this.loadAndShowSVG(svgPath, $altimetriaSection);
      $section.append($altimetriaSection);

      $("main").append($section);
    }
  }

  appendP($parent, label, value) {
    var $p = $("<p></p>");
    if (label) {
      $p.append($("<label></label>").text(label + ": "));
    }
    $p.append(document.createTextNode(value));
    $parent.append($p);
  }

  getTextContent(parent, tagName) {
    var el = parent.getElementsByTagName(tagName)[0];
    return el ? el.textContent.trim() : "";
  }

  loadAndShowKML(kmlPath, $parentSection) {
    fetch(kmlPath)
      .then(this.checkResponse.bind(this, $parentSection))
      .then(this.onKMLTextLoaded.bind(this, $parentSection))
      .catch(this.onKMLTextLoadError.bind(this, $parentSection));
  }

  checkResponse($parentSection, response) {
    if (!response.ok) {
      throw new Error("No se pudo cargar el archivo KML");
    }
    return response.text();
  }

  onKMLTextLoaded($parentSection, kmlText) {
    var parser = new DOMParser();
    var kmlDoc = parser.parseFromString(kmlText, "text/xml");
    var features = [];

    var lineCoords = kmlDoc.querySelector("LineString > coordinates");
    if (lineCoords) {
      var coordsText = lineCoords.textContent.trim();
      var coordsArray = coordsText.split(/\s+/);
      var coords = [];
      for (var i = 0; i < coordsArray.length; i++) {
        var c = coordsArray[i].split(",");
        var lon = parseFloat(c[0]);
        var lat = parseFloat(c[1]);
        coords.push(ol.proj.fromLonLat([lon, lat]));
      }
      var line = new ol.Feature(new ol.geom.LineString(coords));
      line.setStyle(
        new ol.style.Style({
          stroke: new ol.style.Stroke({ color: "red", width: 3 }),
        })
      );
      features.push(line);
    }

    var points = kmlDoc.querySelectorAll("Placemark > Point > coordinates");
    for (var i = 0; i < points.length; i++) {
      var point = points[i];
      var coordsPoint = point.textContent.trim().split(",");
      var lonP = parseFloat(coordsPoint[0]);
      var latP = parseFloat(coordsPoint[1]);
      var coord = ol.proj.fromLonLat([lonP, latP]);
      var feature = new ol.Feature(new ol.geom.Point(coord));
      feature.setStyle(
        new ol.style.Style({
          image: new ol.style.Icon({
            src: "https://cdn-icons-png.flaticon.com/512/684/684908.png",
            scale: 0.05,
            anchor: [0.5, 1],
          }),
        })
      );
      features.push(feature);
    }

    var vectorLayer = new ol.layer.Vector({
      source: new ol.source.Vector({ features: features }),
    });

    var mapSection = document.createElement("section");
    mapSection.setAttribute("data-map", "true");

    $parentSection.append(mapSection);

    var map = new ol.Map({
      target: mapSection,
      layers: [new ol.layer.Tile({ source: new ol.source.OSM() }), vectorLayer],
      view: new ol.View({
        center:
          features.length > 0
            ? features[0].getGeometry().getCoordinates()
            : ol.proj.fromLonLat([-5.7, 43.2]),
        zoom: 13,
      }),
    });

    var extent = vectorLayer.getSource().getExtent();
    map.getView().fit(extent, { padding: [20, 20, 20, 20] });
  }

  onKMLTextLoadError($parentSection, err) {
    this.appendP($parentSection, "", "Error al cargar el KML.");
  }

  loadAndShowSVG(svgPath, $parentSection) {
  var self = this;

  fetch(svgPath)
    .then(self.handleSVGResponse.bind(self, $parentSection))
    .then(self.handleSVGContent.bind(self, $parentSection))
    .catch(self.handleSVGError.bind(self, $parentSection));
}

// Primer método: comprobar la respuesta HTTP
handleSVGResponse($parentSection, response) {
  if (!response.ok) {
    throw new Error("No se pudo cargar el archivo SVG");
  }
  return response.text();
}

// Segundo método: procesar el contenido SVG
handleSVGContent($parentSection, svgContent) {
  var parser = new DOMParser();
  var svgDoc = parser.parseFromString(svgContent, "image/svg+xml");
  var svgElement = svgDoc.documentElement;

  // Eliminar atributos de tamaño fijo para hacer que escale
  svgElement.removeAttribute("width");
  svgElement.removeAttribute("height");

  // Si no tiene viewBox, lo podríamos calcular (opcional)
  if (!svgElement.hasAttribute("viewBox")) {
    // svgElement.setAttribute("viewBox", "0 0 800 400");
  }

  var serializer = new XMLSerializer();
  var cleanedSVG = serializer.serializeToString(svgElement);

  var $svgWrapper = $("<figure></figure>").html(cleanedSVG);
  $parentSection.append($svgWrapper);
}

handleSVGError($parentSection, error) {
  $parentSection.append("<p>Error al cargar el archivo SVG.</p>");
  console.error(error);
}



  onSVGLoadSuccess($parentSection, data) {
    var svgContent = new XMLSerializer().serializeToString(data.documentElement);
    var $svgContainer = $("<figure></figure>").html(svgContent);
    $parentSection.append($svgContainer);
  }

  onSVGLoadError($parentSection) {
    this.appendP($parentSection, "", "No se pudo cargar el perfil de altimetría.");
  }
}

const rutas = new Rutas();
