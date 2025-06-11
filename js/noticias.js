class NoticiasMieres {
    constructor(apiKey, selector) {
        this.apiKey = apiKey;
        this.section = $(selector).eq(0);
        this.article = this.section.find('article');
        this.apiUrl = `https://newsapi.org/v2/everything?q=Mieres+Langreo&language=es&sortBy=publishedAt&apiKey=${this.apiKey}`;
    }

    iniciar() {
        this.cargarNoticias();
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
        const maxNoticias = 6;
        noticias.slice(0, maxNoticias).forEach(noticia => {
            const seccionNoticia = $('<section>');
            const cabecera = $(`<h3>${noticia.title}</h3>`);
            const cuerpo = $('<article>').append(`<p>${noticia.description || ''}</p>`);
            const pie = $('<footer>').append(`<a href="${noticia.url}" target="_blank">Leer más</a>`);

            seccionNoticia.append(cabecera, cuerpo, pie);
            this.article.append(seccionNoticia);
        });
    }
}

class InitNoticias {
    constructor() {
        this.esperarDOM(() => this.iniciar());
    }

    esperarDOM(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    iniciar() {
        const noticias = new NoticiasMieres('9ed1addf608a4ba5bd0de626859fa965', 'main section:nth-of-type(2)');
        noticias.iniciar();
    }
}

new InitNoticias();
