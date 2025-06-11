class MeteorologiaMieres {
    constructor(lat, lon) {
        this.lat = lat;
        this.lon = lon;
        this.url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,temperature_2m_min,weathercode,precipitation_probability_max,windspeed_10m_max,winddirection_10m_dominant,sunrise,sunset&current_weather=true&timezone=Europe/Madrid&lang=es`;

        this.parrafoTemperatura = $('main section p').eq(0);
        this.parrafoDescripcion = $('main section p').eq(1);
        this.articulo = $('main section:nth-of-type(2) article');
    }

    cargarDatos() {
        $.ajax({
            url: this.url,
            method: 'GET',
            success: this.mostrarDatos.bind(this),
            error: this.mostrarError.bind(this)
        });
    }

    mostrarError() {
        this.parrafoTemperatura.text('Temperatura: — °C');
        this.parrafoDescripcion.text('Condición: Error al obtener los datos del tiempo.');
        this.articulo.empty().append('<p>Error al obtener la previsión.</p>');
    }

    mostrarDatos(data) {
        const actual = data.current_weather;
        const descripcion = this.obtenerDescripcionTiempo(actual.weathercode);
        const iconoUrl = this.obtenerIcono(actual.weathercode);

        this.parrafoTemperatura.text(`Temperatura: ${actual.temperature} °C`);

        // Aquí cambiamos a figure con figcaption
        this.parrafoDescripcion.html(`
            <figure>
                <img src="${iconoUrl}" alt="${descripcion}">
                <figcaption>Condición: ${descripcion}</figcaption>
            </figure>
        `);

        this.articulo.empty();
        const dias = data.daily;

        for (let i = 0; i < 7; i++) {
            const fecha = new Date(dias.time[i]).toLocaleDateString('es-ES', {
                weekday: 'long', day: 'numeric', month: 'short'
            });

            const desc = this.obtenerDescripcionTiempo(dias.weathercode[i]);
            const iconoDia = this.obtenerIcono(dias.weathercode[i]);

            const texto = `
                ${fecha}<br>
                Máx: ${dias.temperature_2m_max[i]} °C, Mín: ${dias.temperature_2m_min[i]} °C<br>
                Lluvia: ${dias.precipitation_probability_max[i]}%<br>
                Viento: ${dias.windspeed_10m_max[i]} km/h (${this.obtenerDireccionViento(dias.winddirection_10m_dominant[i])})<br>
                Amanecer: ${new Date(dias.sunrise[i]).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}<br>
                Anochecer: ${new Date(dias.sunset[i]).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}
            `;

            // Aquí también usamos figure para cada día
            const seccionDia = $('<section></section>');
            seccionDia.append(`<p>${texto}</p>`);
            seccionDia.append(`
                <figure>
                    <img src="${iconoDia}" alt="${desc}">
                    <figcaption>${desc}</figcaption>
                </figure>
            `);
            this.articulo.append(seccionDia);
        }
    }

    obtenerDescripcionTiempo(codigo) {
        const descripciones = {
            0: "Despejado", 1: "Mayormente despejado", 2: "Parcialmente nublado", 3: "Nublado",
            45: "Niebla", 48: "Niebla con escarcha",
            51: "Llovizna ligera", 53: "Llovizna moderada", 55: "Llovizna densa",
            61: "Lluvia ligera", 63: "Lluvia moderada", 65: "Lluvia fuerte",
            71: "Nieve ligera", 73: "Nieve moderada", 75: "Nieve intensa",
            80: "Chubascos ligeros", 81: "Chubascos moderados", 82: "Chubascos fuertes",
            95: "Tormenta", 96: "Tormenta con granizo leve", 99: "Tormenta con granizo fuerte"
        };
        return descripciones[codigo] || "Sin datos";
    }

    obtenerDireccionViento(grados) {
        const direcciones = ["N", "NE", "E", "SE", "S", "SO", "O", "NO"];
        const index = Math.round(grados / 45) % 8;
        return direcciones[index];
    }

    obtenerIcono(codigo) {
        return `https://www.weatherbit.io/static/img/icons/${this.mapearCodigoAIcono(codigo)}.png`;
    }

    mapearCodigoAIcono(codigo) {
        const iconMap = {
            0: "c01d", 1: "c02d", 2: "c03d", 3: "c04d",
            45: "a05d", 48: "a05d",
            51: "d01d", 53: "d02d", 55: "d03d",
            61: "r01d", 63: "r02d", 65: "r03d",
            71: "s01d", 73: "s02d", 75: "s03d",
            80: "r04d", 81: "r05d", 82: "r06d",
            95: "t01d", 96: "t04d", 99: "t05d"
        };
        return iconMap[codigo] || "c04d";
    }
}

class ControladorMeteorologia {
    constructor() {
        document.addEventListener('DOMContentLoaded', this.inicializar.bind(this));
    }

    inicializar() {
        const lat = 43.2514;
        const lon = -5.7705;
        const mieres = new MeteorologiaMieres(lat, lon);
        mieres.cargarDatos();
    }
}

new ControladorMeteorologia();
