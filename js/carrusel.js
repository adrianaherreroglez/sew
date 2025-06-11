class Carrusel {
    constructor($section) {
        this.$section = $section;
        this.$article = this.$section.find('article');
        this.$botones = this.$section.find('footer').find('button');

        this.imagenes = [
            'multimedia/imagenes/mapa.png',
            'multimedia/imagenes/mieres1.jpg',
            'multimedia/imagenes/mieres2.jpg',
            'multimedia/imagenes/mieres3.jpg',
            'multimedia/imagenes/mieres4.jpg'
        ];
        this.index = 0;

        this.inicializar();
    }

    inicializar() {
        this.mostrarImagen();
        this.asignarEventos();
    }

    mostrarImagen() {
        this.$article.empty();
        const $img = $('<img>', {
            src: this.imagenes[this.index],
            alt: `Imagen ${this.index + 1}`
        });
        this.$article.append($img);
    }

    asignarEventos() {
        this.$botones.eq(0).on('click', this.anterior.bind(this));
        this.$botones.eq(1).on('click', this.siguiente.bind(this));
    }

    anterior() {
        this.index = (this.index - 1 + this.imagenes.length) % this.imagenes.length;
        this.mostrarImagen();
    }

    siguiente() {
        this.index = (this.index + 1) % this.imagenes.length;
        this.mostrarImagen();
    }
}

class App {
    constructor() {
        this.intento = 0;
        this.maxIntentos = 100; // Por si nunca carga
        this.buscarMain();
    }

    buscarMain() {
        this.intervalo = setInterval(this.verificarMain.bind(this), 50);
    }

    verificarMain() {
        const $main = $('main');
        if ($main.length > 0 || this.intento > this.maxIntentos) {
            clearInterval(this.intervalo);
            if ($main.length > 0) {
                const $seccion = $main.find('section').eq(0);
                new Carrusel($seccion);
            }
        }
        this.intento++;
    }
}

new App();
