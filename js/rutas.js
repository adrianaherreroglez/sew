class Rutas {
    constructor() {
        // Constructor vacío, no necesitamos variables globales
    }
    
    printXMLInfo(files) {
    if (files.length === 0) {
        alert('No ha seleccionado ningún archivo XML');
        return;
    }
    // Ocultar el label 'Introduzca XML:'
    const main = document.getElementsByTagName('main')[0];
    const label = main.getElementsByTagName('label')[0];
    if (label) label.style.display = 'none';

    const file = files[0];
    const reader = new FileReader();
    reader.onload = (e) => {
        const xmlStr = e.target.result;
        this.parseXML(xmlStr);
        this.loadAndShowKML('planimetria1.kml');
    };
    reader.readAsText(file);
}



    parseXML(xmlStr) {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xmlStr, "text/xml");

        if (xmlDoc.getElementsByTagName("parsererror").length > 0) {
            alert("Error al parsear el XML");
            return;
        }

        const rutas = xmlDoc.getElementsByTagName("ruta");
        if (rutas.length === 0) {
            alert("No se encontraron rutas en el XML");
            return;
        }

        const main = document.getElementsByTagName('main')[0];

        while (main.childNodes.length > 2) {
            main.removeChild(main.lastChild);
        }

        for (let i = 0; i < rutas.length; i++) {
            const ruta = rutas[i];

            
            const rutaDiv = document.createElement('section');

            // Nombre
            const nombre = this.getTextContent(ruta, 'nombre');
            const h2 = document.createElement('h2');
            h2.textContent = nombre;
            rutaDiv.appendChild(h2);

            // Tipo
            this.appendP(rutaDiv, 'Tipo', this.getTextContent(ruta, 'tipo'));

            // Transporte
            this.appendP(rutaDiv, 'Transporte', this.getTextContent(ruta, 'transporte'));

            // Fecha Inicio (opcional)
            const fechaInicio = this.getTextContent(ruta, 'fechaInicio');
            if (fechaInicio) this.appendP(rutaDiv, 'Fecha Inicio', fechaInicio);

            // Hora Inicio (opcional)
            const horaInicio = this.getTextContent(ruta, 'horaInicio');
            if (horaInicio) this.appendP(rutaDiv, 'Hora Inicio', horaInicio);

            // Duración
            this.appendP(rutaDiv, 'Duración', this.getTextContent(ruta, 'duracion'));

            // Agencia
            this.appendP(rutaDiv, 'Agencia', this.getTextContent(ruta, 'agencia'));

            // Descripción
            this.appendP(rutaDiv, 'Descripción', this.getTextContent(ruta, 'descripcion'));

            // Personas adecuadas
            this.appendP(rutaDiv, 'Personas adecuadas', this.getTextContent(ruta, 'personasAdecuadas'));

            // Lugar de inicio
            this.appendP(rutaDiv, 'Lugar de inicio', this.getTextContent(ruta, 'lugarInicio'));

            // Dirección inicio
            this.appendP(rutaDiv, 'Dirección inicio', this.getTextContent(ruta, 'direccionInicio'));

            // Referencias
            const referencias = ruta.getElementsByTagName('referencia');
            if (referencias.length > 0) {
                const h3ref = document.createElement('h3');
                h3ref.textContent = 'Referencias';
                rutaDiv.appendChild(h3ref);

                const ul = document.createElement('ul');
                for (let j = 0; j < referencias.length; j++) {
                    const url = referencias[j].textContent;
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.href = url;
                    a.textContent = url;
                    li.appendChild(a);
                    ul.appendChild(li);
                }
                rutaDiv.appendChild(ul);
            }

            // Hitos
            const hitos = ruta.getElementsByTagName('hito');
            if (hitos.length > 0) {
                const h3hitos = document.createElement('h3');
                h3hitos.textContent = 'Hitos';
                rutaDiv.appendChild(h3hitos);

                for (let k = 0; k < hitos.length; k++) {
                    const hito = hitos[k];

                    const hitoDiv = document.createElement('section');

                    // Nombre hito
                    const h4 = document.createElement('h4');
                    h4.textContent = this.getTextContent(hito, 'nombre');
                    hitoDiv.appendChild(h4);

                    // Descripción hito
                    this.appendP(hitoDiv, '', this.getTextContent(hito, 'descripcion'));

                    // Distancia con unidad
                    const distanciaEl = hito.getElementsByTagName('distancia')[0];
                    if (distanciaEl) {
                        const distancia = distanciaEl.textContent;
                        const unidad = distanciaEl.getAttribute('unidad') || '';
                        this.appendP(hitoDiv, 'Distancia', `${distancia} ${unidad}`);
                    }

                    // Galería fotos
                    const fotos = hito.getElementsByTagName('foto');
                    if (fotos.length > 0) {
                        const fotosDiv = document.createElement('section');
                        for (let f = 0; f < fotos.length; f++) {
                            const img = document.createElement('img');
                            img.src = fotos[f].textContent;
                            img.alt = `Foto de ${h4.textContent}`;
                            fotosDiv.appendChild(img);
                        }
                        hitoDiv.appendChild(fotosDiv);
                    }

                    rutaDiv.appendChild(hitoDiv);
                }
            }

            const kmlFile = this.getTextContent(ruta, 'kml');
                if (kmlFile) {
                this.loadAndShowKML(kmlFile, rutaDiv);
            }

            const altimetriaFile = `xml/altimetria${i + 1}.svg`;
                fetch(altimetriaFile)
                .then(response => response.text())
                .then(svgText => {
                    const svgContainer = document.createElement('section');
                    svgContainer.innerHTML = svgText;
                    rutaDiv.appendChild(svgContainer);
                        })
                    .catch(error => {
                    const p = document.createElement('p');
                    p.textContent = 'No se pudo cargar el perfil de altimetría.';
                     rutaDiv.appendChild(p);
            });

            main.appendChild(rutaDiv);
        }
    }

    appendP(parent, label, text) {
        const p = document.createElement('p');
        if (label) {
            const li = document.createElement('li');
            li.textContent = label + ': ';
            p.appendChild(li);
        }
        p.appendChild(document.createTextNode(text));
        parent.appendChild(p);
    }

    getTextContent(parent, tagName) {
        const el = parent.getElementsByTagName(tagName)[0];
        return el ? el.textContent.trim() : '';
    }

    loadAndShowKML(kmlPath, parentElement) {
    fetch(kmlPath)
        .then(response => {
            if (!response.ok) throw new Error("No se pudo cargar el archivo KML");
            return response.text();
        })
        .then(kmlText => {
            const parser = new DOMParser();
            const kmlDoc = parser.parseFromString(kmlText, "text/xml");

            const features = [];

            const lineStringElement = kmlDoc.querySelector("LineString > coordinates");
            if (lineStringElement) {
                const coordTextLine = lineStringElement.textContent.trim();
                const coordLines = coordTextLine.split(/\s+/);
                const lineCoordinates = coordLines.map(line => {
                    const [lon, lat, alt] = line.split(',').map(Number);
                    return ol.proj.fromLonLat([lon, lat]);
                });

                const lineString = new ol.geom.LineString(lineCoordinates);
                const lineFeature = new ol.Feature({ geometry: lineString });
                lineFeature.setStyle(new ol.style.Style({
                    stroke: new ol.style.Stroke({ color: '#FF0000', width: 4 })
                }));
                features.push(lineFeature);
            }

            // Procesar los puntos (Placemark > Point)
            const pointElements = kmlDoc.querySelectorAll("Placemark > Point > coordinates");
            pointElements.forEach(coordEl => {
                const coordText = coordEl.textContent.trim();
                const [lon, lat, alt] = coordText.split(',').map(Number);
                const coord = ol.proj.fromLonLat([lon, lat]);
                const pointFeature = new ol.Feature(new ol.geom.Point(coord));

                pointFeature.setStyle(new ol.style.Style({
                    image: new ol.style.Icon({
                        anchor: [0.5, 1],
                        src: 'https://cdn-icons-png.flaticon.com/512/684/684908.png', // símbolo de ubicación
                        scale: 0.07
                    })
                }));
                features.push(pointFeature);
            });

            // Capa vectorial
            const vectorLayer = new ol.layer.Vector({
                source: new ol.source.Vector({ features })
            });

            // Contenedor de mapa
            const mapSection = document.createElement('section');
            mapSection.style.height = '400px';
            mapSection.style.marginTop = '1em';
            parentElement.appendChild(mapSection);

            // Crear el mapa
            const map = new ol.Map({
                target: mapSection,
                layers: [
                    new ol.layer.Tile({ source: new ol.source.OSM() }),
                    vectorLayer
                ],
                view: new ol.View({
                    center: features.length > 0 ? features[0].getGeometry().getCoordinates() : ol.proj.fromLonLat([-5.7, 43.21]),
                    zoom: 14
                })
            });

            // Ajustar el zoom para que se vean todos los elementos
            const extent = vectorLayer.getSource().getExtent();
            map.getView().fit(extent, { padding: [30, 30, 30, 30] });
        })
        .catch(error => {
            const p = document.createElement('p');
            p.textContent = 'Error al cargar el mapa: ' + error.message;
            parentElement.appendChild(p);
        });
}



}

const rutas = new Rutas();
