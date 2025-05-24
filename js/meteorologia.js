$(document).ready(function () {
    const lat = 43.2514;
    const lon = -5.7705;
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,temperature_2m_min,weathercode,precipitation_probability_max,windspeed_10m_max,winddirection_10m_dominant,sunrise,sunset&current_weather=true&timezone=Europe/Madrid&lang=es`;

    const spans = $('span');
    const ul = $('ul');

    $.ajax({
        url: url,
        method: 'GET',
        success: function (data) {
            // Tiempo actual
            const tempActual = data.current_weather.temperature;
            const codigo = data.current_weather.weathercode;
            const descripcion = obtenerDescripcionTiempo(codigo);
            spans.eq(0).text(tempActual);
            spans.eq(1).text(descripcion);

            // Previsión diaria con más detalles
            const dias = data.daily;
            for (let i = 0; i < 7; i++) {
                const fecha = new Date(dias.time[i]).toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'short' });

                const max = dias.temperature_2m_max[i];
                const min = dias.temperature_2m_min[i];
                const desc = obtenerDescripcionTiempo(dias.weathercode[i]);
                const viento = dias.windspeed_10m_max[i];
                const direccion = obtenerDireccionViento(dias.winddirection_10m_dominant[i]);
                const lluvia = dias.precipitation_probability_max[i];
                const salidaSol = new Date(dias.sunrise[i]).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                const puestaSol = new Date(dias.sunset[i]).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

                ul.append(`
                    <li>
                        <strong>${fecha}</strong>: ${desc}<br>
                        Máx: ${max}°C, Mín: ${min}°C<br>
                        Lluvia: ${lluvia}%<br>
                        Viento: ${viento} km/h (${direccion})<br>
                        🌅 ${salidaSol} - 🌇 ${puestaSol}
                    </li>
                `);
            }
        },
        error: function () {
            $('body').append('<p>Error al obtener los datos del tiempo.</p>');
        }
    });

    function obtenerDescripcionTiempo(codigo) {
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

    function obtenerDireccionViento(grados) {
        const direcciones = ["N", "NE", "E", "SE", "S", "SO", "O", "NO"];
        const index = Math.round(grados / 45) % 8;
        return direcciones[index];
    }
});
