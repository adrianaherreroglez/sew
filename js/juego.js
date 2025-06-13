class JuegoTest {
    constructor(preguntas) {
        this.preguntas = preguntas;
        this.aciertos = 0;

        this.$form = $('main section').first().find('form');
        this.$resultado = $('main section').last();
        this.$boton = $('main > button');

        this.mostrarPreguntas();
        this.asignarEventos();
    }

    mostrarPreguntas() {
        for (let i = 0; i < this.preguntas.length; i++) {
            const pregunta = this.preguntas[i];
            const $fieldset = $('<fieldset>');
            const $legend = $('<legend>').text(`${i + 1}. ${pregunta.pregunta}`);
            $fieldset.append($legend);

            for (let j = 0; j < pregunta.opciones.length; j++) {
                const $label = $('<label>');
                const $radio = $('<input>', {
                    type: 'radio',
                    name: `pregunta${i}`,
                    value: j
                });
                $label.append($radio).append(` ${pregunta.opciones[j]}`);
                const $p = $('<p>');
                $p.append($label);
                $fieldset.append($p);

            }

            this.$form.append($fieldset);
        }
    }

    asignarEventos() {
        this.$boton.on('click', this.comprobarRespuestas.bind(this));
    }

    comprobarRespuestas() {
        this.aciertos = 0;

        for (let i = 0; i < this.preguntas.length; i++) {
            const $respuesta = this.$form.find(`input[name="pregunta${i}"]:checked`);
            if ($respuesta.length === 0) {
                window.alert(`Debes responder la pregunta ${i + 1}`);
                return;
            }
            const valor = parseInt($respuesta.val());
            if (valor === this.preguntas[i].correcta) {
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

class JuegoApp {
    constructor() {
        this.intentos = 0;
        this.maxIntentos = 100;
        this.iniciarCuandoMainEsteDisponible();
    }

    iniciarCuandoMainEsteDisponible() {
        this.intervalo = setInterval(this.verificarMain.bind(this), 50);
    }

    verificarMain() {
        const $main = $('main');
        if ($main.length > 0 || this.intentos >= this.maxIntentos) {
            clearInterval(this.intervalo);
            if ($main.length > 0) {
                this.iniciarJuego();
            }
        }
        this.intentos++;
    }

    iniciarJuego() {
        const preguntas = this.obtenerPreguntas();
        new JuegoTest(preguntas);
    }

    obtenerPreguntas() {
        return [
            {
                pregunta: "¿Cuantas rutas aparecen recomendadas en este sitio web?",
                opciones: ["1", "0", "3", "4", "5"],
                correcta: 2
            },
            {
                pregunta: "¿Qué se destaca en la gastronomía de Mieres?",
                opciones: ["Cocido", "Fabada asturiana", "Paella", "Gazpacho", "Pulpo"],
                correcta: 1
            },
            {
                pregunta: "¿Cuantas noticias aparecen en la página inicial?",
                opciones: ["2", "3", "6", "7", "5"],
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
                pregunta: "¿Cuantos recursos turísticos muestra la sección de reservas?",
                opciones: ["5", "3", "10", "7", "9"],
                correcta: 0
            },
            {
                pregunta: "¿Qué ingrediente típico se menciona en la comida local como uno de los más utilizados?",
                opciones: ["Tofu", "Trigo", "Maíz", "Morcilla", "Tomates cherry"],
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
    }
}

new JuegoApp();
