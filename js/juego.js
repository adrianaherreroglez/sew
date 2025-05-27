class JuegoTest {
    constructor(preguntas) {
        this.preguntas = preguntas;
        this.aciertos = 0;

        this.$form = $('main section').first().find('form');
        this.$resultado = $('main section').last();
        this.$boton = $('button');

        this.mostrarPreguntas();
        this.$boton.on('click', () => this.comprobarRespuestas());
    }

    mostrarPreguntas() {
        this.preguntas.forEach((q, i) => {
            const $fieldset = $('<fieldset>');
            const $legend = $('<legend>').text(`${i + 1}. ${q.pregunta}`);
            $fieldset.append($legend);

            q.opciones.forEach((opcion, j) => {
                const $label = $('<label>');
                const $radio = $('<input>', {
                    type: 'radio',
                    name: `pregunta${i}`,
                    value: j
                });
                $label.append($radio).append(` ${opcion}`);
                $fieldset.append($label).append('<br>');
            });

            this.$form.append($fieldset);
        });
    }

    comprobarRespuestas() {
        this.aciertos = 0;

        for (let i = 0; i < this.preguntas.length; i++) {
            const respuesta = this.$form.find(`input[name="pregunta${i}"]:checked`).val();

            if (respuesta === undefined) {
                alert(`Debes responder la pregunta ${i + 1}`);
                return;
            }

            if (parseInt(respuesta) === this.preguntas[i].correcta) {
                this.aciertos++;
            }
        }

        this.mostrarResultado();
    }

    mostrarResultado() {
        this.$resultado.empty();
        this.$resultado.append(`<p>Tu puntuación: ${this.aciertos} / ${this.preguntas.length}</p>`);
    }
}

$(document).ready(() => {
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

    new JuegoTest(preguntas);
});
