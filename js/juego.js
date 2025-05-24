document.addEventListener('DOMContentLoaded', () => {
    const preguntas = [
        {
            pregunta: "¿Cuál es la capital del concejo de Mieres?",
            opciones: ["Oviedo", "Gijón", "Mieres", "Avilés", "Langreo"],
            correcta: 2
        },
        {
            pregunta: "¿Qué se destaca en la gastronomía de Mieres?",
            opciones: ["Cocido madrileño", "Fabada asturiana", "Paella", "Gazpacho", "Pulpo a la gallega"],
            correcta: 1
        },
        {
            pregunta: "¿Qué tipo de rutas se pueden hacer en Mieres?",
            opciones: ["Marítimas", "Desérticas", "De montaña", "Subterráneas", "Urbanas únicamente"],
            correcta: 2
        },
        {
            pregunta: "¿Qué servicio muestra el clima actual en el sitio?",
            opciones: ["ClimaYa", "AEMET", "Open-Meteo", "MeteoMieres", "Yahoo Weather"],
            correcta: 2
        },
        {
            pregunta: "¿Cuántos días de previsión meteorológica se ofrecen?",
            opciones: ["3", "5", "7", "10", "14"],
            correcta: 2
        },
        {
            pregunta: "¿Qué formato tiene la sección de reservas?",
            opciones: ["Formulario en HTML", "PDF descargable", "Correo automático", "Documento Word", "Chat en vivo"],
            correcta: 0
        },
        {
            pregunta: "¿Qué ingrediente típico se menciona en la comida local?",
            opciones: ["Tofu", "Trigo", "Maíz", "Fabes", "Tomates cherry"],
            correcta: 3
        },
        {
            pregunta: "¿En qué idioma se encuentra el contenido del sitio?",
            opciones: ["Inglés", "Francés", "Español", "Alemán", "Italiano"],
            correcta: 2
        },
        {
            pregunta: "¿Qué secciones hay en el menú principal?",
            opciones: ["Inicio, Clima, Juegos", "Inicio, Reservas, Contacto", "Inicio, Gastronomía, Rutas, Meteorología, Juego, Reservas, Ayuda", "Inicio, Noticias, Chat", "Inicio, Cultura, Deportes"],
            correcta: 2
        },
        {
            pregunta: "¿Dónde se muestran las noticias relacionadas con Mieres?",
            opciones: ["En el pie de página", "En la página de inicio", "En una sección dedicada", "En meteorología", "No hay noticias"],
            correcta: 2
        }
    ];

    const form = document.querySelector('form');
    const button = document.querySelector('button');
    const resultadoSection = document.querySelector('section');

    preguntas.forEach((q, i) => {
        const fieldset = document.createElement('fieldset');

        const legend = document.createElement('legend');
        legend.textContent = `${i + 1}. ${q.pregunta}`;
        fieldset.appendChild(legend);

        q.opciones.forEach((opcion, j) => {
            const label = document.createElement('label');
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = `pregunta${i}`;
            radio.value = j;
            label.appendChild(radio);
            label.append(` ${opcion}`);
            fieldset.appendChild(label);
            fieldset.appendChild(document.createElement('br'));
        });

        form.appendChild(fieldset);
    });

    button.addEventListener('click', () => {
        let aciertos = 0;
        for(let i = 0; i < preguntas.length; i++) {
            const seleccionada = form.querySelector(`input[name="pregunta${i}"]:checked`);
            if(!seleccionada) {
                alert(`Debes responder la pregunta ${i+1}`);
                return;
            }
            if(parseInt(seleccionada.value) === preguntas[i].correcta) {
                aciertos++;
            }
        }
        // Limpiar resultados previos
        resultadoSection.textContent = '';
        // Mostrar resultado
        const p = document.createElement('p');
        p.textContent = `Tu puntuación: ${aciertos} / ${preguntas.length}`;
        resultadoSection.appendChild(p);
    });
});
