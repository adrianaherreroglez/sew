class NoticiasMieres {
    constructor(apiKey) {
        this.apiKey = apiKey;
        this.section = $('section').eq(1);
        this.article = this.section.find('article');
        this.apiUrl = `https://newsapi.org/v2/everything?q=Mieres+Asturias&language=es&sortBy=publishedAt&apiKey=${this.apiKey}`;
    }

    cargarNoticias() {
        $.ajax({
            url: this.apiUrl,
            method: 'GET',
            success: (data) => {
                this.mostrarNoticias(data.articles);
            },
            error: () => {
                this.article.append('<p>No se pudieron cargar las noticias.</p>');
            }
        });
    }

    mostrarNoticias(noticias) {
        noticias.slice(0, 5).forEach(noticia => {
            const seccionNoticia = $('<section>');
            const cabecera = $('<header>').append(`<h3>${noticia.title}</h3>`);
            const cuerpo = $('<article>').append(`<p>${noticia.description || ''}</p>`);
            const pie = $('<footer>').append(`<a href="${noticia.url}" target="_blank">Leer más</a>`);

            seccionNoticia.append(cabecera, cuerpo, pie);
            this.article.append(seccionNoticia);
        });
    }
}

// Document ready para Noticias
$(document).ready(function () {
    const noticias = new NoticiasMieres('9ed1addf608a4ba5bd0de626859fa965'); 
    noticias.cargarNoticias();
});


