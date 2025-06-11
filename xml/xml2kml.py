import xml.etree.ElementTree as ET

class Kml:
    def __init__(self):
        self.raiz = ET.Element('kml', xmlns="http://www.opengis.net/kml/2.2")
        self.doc = ET.SubElement(self.raiz, 'Document')

    def addPlacemarkPoint(self, nombre, descripcion, long, lat, alt, modoAltitud='relativeToGround'):
        pm = ET.SubElement(self.doc, 'Placemark')
        ET.SubElement(pm, 'name').text = nombre
        ET.SubElement(pm, 'description').text = descripcion
        punto = ET.SubElement(pm, 'Point')
        ET.SubElement(punto, 'coordinates').text = f"{long},{lat},{alt}"
        ET.SubElement(punto, 'altitudeMode').text = modoAltitud

    def addPlacemarkLineString(self, nombre, coords_str, modoAltitud='relativeToGround', color='#ff0000ff', ancho='4'):
        pm = ET.SubElement(self.doc, 'Placemark')
        ET.SubElement(pm, 'name').text = nombre
        estilo = ET.SubElement(pm, 'Style')
        linea = ET.SubElement(estilo, 'LineStyle')
        ET.SubElement(linea, 'color').text = color
        ET.SubElement(linea, 'width').text = ancho

        ls = ET.SubElement(pm, 'LineString')
        ET.SubElement(ls, 'extrude').text = '1'
        ET.SubElement(ls, 'tessellation').text = '1'
        ET.SubElement(ls, 'coordinates').text = coords_str
        ET.SubElement(ls, 'altitudeMode').text = modoAltitud

    def escribir(self, nombreArchivoKML):
        arbol = ET.ElementTree(self.raiz)
        arbol.write(nombreArchivoKML, encoding='utf-8', xml_declaration=True)


def extraer_coordenadas_por_ruta(archivoXML):
    try:
        tree = ET.parse(archivoXML)
    except (IOError, ET.ParseError) as e:
        print(f"Error al procesar el archivo XML: {e}")
        exit()

    root = tree.getroot()
    rutas = root.findall("ruta")

    for i, ruta in enumerate(rutas, start=1):
        coordenadas = []
        hitos_data = []

        hitos = ruta.find("hitos")
        if hitos is None:
            print(f"La ruta {i} no tiene hitos.")
            continue

        for hito in hitos.findall("hito"):
            coord = hito.find("coordenadas")
            if coord is not None:
                long_elem = coord.find("longitud")
                lat_elem = coord.find("latitud")
                alt_elem = coord.find("altitud")

                if long_elem is not None and lat_elem is not None and alt_elem is not None:
                    long = long_elem.text.strip()
                    lat = lat_elem.text.strip()
                    alt = alt_elem.text.strip()
                    coordenadas.append(f"{long},{lat},{alt}")
                    nombre_hito = hito.find("nombre").text if hito.find("nombre") is not None else "Hito"
                    descripcion_hito = hito.find("descripcion").text if hito.find("descripcion") is not None else ""
                    hitos_data.append((nombre_hito, descripcion_hito, long, lat, alt))

        if not coordenadas:
            print(f"No se encontraron coordenadas para la ruta {i}")
            continue

        # Añadimos la primera coordenada al final para cerrar la ruta circular
        coordenadas.append(coordenadas[0])

        # Crear KML
        kml = Kml()

        # Añadir LineString de la ruta
        coords_text = " ".join(coordenadas)
        kml.addPlacemarkLineString(f"Ruta {i}", coords_text)

        # Añadir puntos para cada hito
        for nombre_hito, descripcion_hito, long, lat, alt in hitos_data:
            kml.addPlacemarkPoint(nombre_hito, descripcion_hito, long, lat, alt)

        nombre_archivo = f"planimetria{i}.kml"
        kml.escribir(nombre_archivo)
        print(f"Archivo generado: {nombre_archivo}")


if __name__ == "__main__":
    extraer_coordenadas_por_ruta("rutas.xml")
