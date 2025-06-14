class Noticias {
    constructor() {
        this.apiKey = 'pub_43c3e4f9b83448edac64b1557d2cd5a0';
        this.apiUrl = 'https://newsdata.io/api/1/news?q=Mieres&language=es&apikey=' + this.apiKey;
        this.cargarNoticias();
    }

    cargarNoticias() {
        $.ajax({
            url: this.apiUrl,
            method: 'GET',
            dataType: 'json',
            success: this.mostrarNoticias.bind(this),
            error: this.mostrarError.bind(this)
        });
    }

    mostrarNoticias(data) {
        var noticias = data.results || [];
        var contenedor = $("main>section:last-of-type>article");
        contenedor.empty();

        if (noticias.length === 0) {
            contenedor.append('<p>No se encontraron noticias para "Mieres".</p>');
            return;
        }

        var limite = 6;
        for (var i = 0; i < noticias.length && i < limite; i++) {
            var noticia = noticias[i];
            var noticiaHTML = '<section>' +
                '<h3>' + (noticia.title || 'Sin título') + '</h3>' +
                '<article><p>' + (noticia.description || '') + '</p></article>' +
                '<footer><a href="' + (noticia.link || '#') + '" target="_blank">Leer más</a></footer>' +
                '</section>';

            contenedor.append(noticiaHTML);
        }
    }

    mostrarError() {
        var contenedor = $("main>section:last-of-type>article");
        contenedor.empty();
        contenedor.append('<p>No se pudieron cargar las noticias.</p>');
    }
}

new Noticias();
