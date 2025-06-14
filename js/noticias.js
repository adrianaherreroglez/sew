class Noticias {
    constructor() {
        this.apiKey = '9ed1addf608a4ba5bd0de626859fa965';
        this.apiUrl = 'https://newsapi.org/v2/everything?q=Mieres+Langreo&language=es&sortBy=publishedAt&apiKey=' + this.apiKey;
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
        var noticias = data.articles;
        var contenedor = $("main>section:last-of-type>article");
        contenedor.empty();

        for (var i = 0; i < noticias.length; i++) {
            var noticia = noticias[i];
            var noticiaHTML = '<section>' +
                '<h3>' + noticia.title + '</h3>' +
                '<article><p>' + (noticia.description || '') + '</p></article>' +
                '<footer><a href="' + noticia.url + '" target="_blank">Leer más</a></footer>' +
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

// Simplemente instanciamos la clase:
new Noticias();
