class Carrusel {
    constructor() {
        this.section = $('section').eq(0);
        this.article = this.section.find('article');
        this.imagenes = [
            'multimedia/mapa.png',
            'multimedia/mieres1.jpg',
            'multimedia/mieres2.jpg',
            'multimedia/mieres3.jpg',
            'multimedia/mieres4.jpg',
            'multimedia/mieres5.jpg'
        ];
        this.index = 0;
        this.mostrarImagen();

        const botones = this.section.find('footer').find('button');
        botones.eq(0).on('click', () => this.anterior());
        botones.eq(1).on('click', () => this.siguiente());
    }

    mostrarImagen() {
        this.article.empty();
        const imagen = $('<img>', {
            src: this.imagenes[this.index],
            alt: `Imagen ${this.index + 1}`
        });
        this.article.append(imagen);
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

// Document ready para Carrusel
$(document).ready(function () {
    new Carrusel();
});
