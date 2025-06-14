class Noticias {
    constructor() {
        this.apiKey = '7a2c4f271191fe2fa07a4179a1d62229';
        this.apiUrl = 'https://gnews.io/api/v4/search?q=Mieres&lang=es&max=6&token=' + this.apiKey;
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
        var noticias = data.articles || [];
        var contenedor = $("main>section:last-of-type>article");
        contenedor.empty();

        if (noticias.length === 0) {
            contenedor.append('<p>No se encontraron noticias.</p>');
            return;
        }

        for (var i = 0; i < noticias.length; i++) {
            var noticia = noticias[i];
            var descripcion = noticia.description || '';
            if (descripcion.length > 150) {
                descripcion = descripcion.substring(0, 150).trim() + '...';
            }

            var noticiaHTML = '<section>' +
                '<h3>' + noticia.title + '</h3>' +
                '<article><p>' + descripcion + '</p></article>' +
                '<footer><a href="' + noticia.url + '" target="_blank" rel="noopener noreferrer">Leer más</a></footer>' +
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
