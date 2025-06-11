import xml.etree.ElementTree as ET
import os

SVG_WIDTH = 800
SVG_HEIGHT = 300
MARGIN = 40
ZERO_LINE_COLOR = "#888"
ALTIMETRY_COLOR = "#007acc"
HITO_LABEL_COLOR = "#000"

def extraer_datos_altimetria(xml_file):
    tree = ET.parse(xml_file)
    root = tree.getroot()
    rutas = root.findall("ruta")
    return rutas

def generar_svg_altimetria(ruta, indice):
    hitos = ruta.find("hitos")
    if hitos is None:
        print(f"Ruta {indice} no contiene hitos.")
        return

    altitudes = []
    nombres = []

    for hito in hitos.findall("hito"):
        nombre = hito.findtext("nombre", "Hito")
        coord = hito.find("coordenadas")
        alt = coord.findtext("altitud") if coord is not None else None

        if alt is not None:
            try:
                altitud = float(alt.strip())
                altitudes.append(altitud)
                nombres.append(nombre.strip())
            except ValueError:
                continue

    if len(altitudes) < 2:
        print(f"Ruta {indice} tiene pocos datos para generar altimetría.")
        return

    max_alt = max(altitudes)
    min_alt = min(min(altitudes), 0)
    alt_range = max_alt - min_alt or 1

    n_puntos = len(altitudes)
    step_x = (SVG_WIDTH - 2 * MARGIN) / (n_puntos - 1)

    puntos = []
    for i, alt in enumerate(altitudes):
        x = MARGIN + i * step_x
        y = SVG_HEIGHT - MARGIN - ((alt - min_alt) / alt_range) * (SVG_HEIGHT - 2 * MARGIN)
        puntos.append((x, y))

    ruta_svg = f"altimetria{indice}.svg"
    with open(ruta_svg, "w", encoding="utf-8") as f:
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write(f'<svg xmlns="http://www.w3.org/2000/svg" width="{SVG_WIDTH}" height="{SVG_HEIGHT}">\n')

        # Línea base cota cero
        zero_y = SVG_HEIGHT - MARGIN - ((0 - min_alt) / alt_range) * (SVG_HEIGHT - 2 * MARGIN)
        f.write(f'<line x1="{MARGIN}" y1="{zero_y}" x2="{SVG_WIDTH - MARGIN}" y2="{zero_y}" stroke="{ZERO_LINE_COLOR}" stroke-dasharray="4"/>\n')

        # Línea de altimetría
        path_d = "M " + " L ".join(f"{x:.2f},{y:.2f}" for x, y in puntos)
        f.write(f'<path d="{path_d}" fill="none" stroke="{ALTIMETRY_COLOR}" stroke-width="2"/>\n')

        # Etiquetas de hitos
        for (x, y), nombre in zip(puntos, nombres):
            f.write(f'<circle cx="{x}" cy="{y}" r="3" fill="{ALTIMETRY_COLOR}" />\n')
            f.write(f'<text x="{x}" y="{y - 8}" font-size="10" fill="{HITO_LABEL_COLOR}" text-anchor="middle">{nombre}</text>\n')

        f.write('</svg>\n')

    print(f"Altimetría generada: {ruta_svg}")

def procesar_rutas_y_generar_altimetrias(xml_file):
    rutas = extraer_datos_altimetria(xml_file)
    for idx, ruta in enumerate(rutas, start=1):
        generar_svg_altimetria(ruta, idx)

if __name__ == "__main__":
    procesar_rutas_y_generar_altimetrias("rutas.xml")
