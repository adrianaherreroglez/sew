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
            success: this.mostrarNoticias.bind(this),
            error: this.mostrarError.bind(this)
        });
    }

    mostrarNoticias(data) {
        const noticias = data.articles;
        const maxNoticias = 6;

        for (let i = 0; i < Math.min(noticias.length, maxNoticias); i++) {
            const noticia = noticias[i];
            const seccionNoticia = $('<section>');
            const cabecera = $(`<h3>${noticia.title}</h3>`);
            const cuerpo = $('<article>').append(`<p>${noticia.description || ''}</p>`);
            const pie = $('<footer>').append(`<a href="${noticia.url}" target="_blank">Leer más</a>`);

            seccionNoticia.append(cabecera, cuerpo, pie);
            this.article.append(seccionNoticia);
        }
    }

    mostrarError() {
        this.article.append('<p>No se pudieron cargar las noticias.</p>');
    }
}


class InitNoticias {
    constructor() {
        this.notificador = null;
        this.apiKey = '9ed1addf608a4ba5bd0de626859fa965';
        this.selector = 'main section:nth-of-type(2)';
        this.inicializar();
    }

    inicializar() {
        // Sin pasar funciones anónimas: se llama a método de instancia
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', this.iniciar.bind(this));
        } else {
            this.iniciar();
        }
    }

    iniciar() {
        this.notificador = new NoticiasMieres(this.apiKey, this.selector);
        this.notificador.iniciar();
    }
}

new InitNoticias();
