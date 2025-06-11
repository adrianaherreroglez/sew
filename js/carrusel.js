class Carrusel {
    constructor(selector) {
        this.$section = $(selector).eq(0);
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
        this.botones();
    }

    mostrarImagen() {
        this.$article.empty();
        const $img = $('<img>', {
            src: this.imagenes[this.index],
            alt: `Imagen ${this.index + 1}`
        });
        this.$article.append($img);
    }

    botones() {
        this.$botones.eq(0).on('click', () => this.anterior());
        this.$botones.eq(1).on('click', () => this.siguiente());
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
        this.esperarDOM(() => {
            this.carrusel = new Carrusel('main section');
        });
    }

    esperarDOM(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }
}

new App();
