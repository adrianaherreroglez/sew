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

        // Ahora cargamos el KML tras procesar el XML
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

        // Seleccionar el <main> (único) para mostrar las rutas
        const main = document.getElementsByTagName('main')[0];

        // Antes de mostrar, borrar todo el contenido salvo el label y input
        // Suponemos que el label y el input son los primeros hijos del main
        // Vamos a dejar solo esos dos y eliminar el resto
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
                    a.target = '_blank';
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

            main.appendChild(rutaDiv);
        }
    }

    appendP(parent, label, text) {
        const p = document.createElement('p');
        if (label) {
            const strong = document.createElement('strong');
            strong.textContent = label + ': ';
            p.appendChild(strong);
        }
        p.appendChild(document.createTextNode(text));
        parent.appendChild(p);
    }

    getTextContent(parent, tagName) {
        const el = parent.getElementsByTagName(tagName)[0];
        return el ? el.textContent.trim() : '';
    }

}

const rutas = new Rutas();
