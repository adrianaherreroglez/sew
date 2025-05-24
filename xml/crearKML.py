import xml.etree.ElementTree as ET

def crear_kml(ruta):
    kml = ET.Element('kml', xmlns="http://www.opengis.net/kml/2.2")
    document = ET.SubElement(kml, 'Document')

    placemark = ET.SubElement(document, 'Placemark')
    name = ET.SubElement(placemark, 'name')
    name.text = ruta['nombre']

    # Agregar coordenadas (lat, long)
    coordinates = ET.SubElement(placemark, 'coordinates')
    coordinates.text = f"{ruta['coordenadas']['longitud']},{ruta['coordenadas']['latitud']}"

    # Guardar el archivo KML
    tree = ET.ElementTree(kml)
    tree.write('ruta1.kml')

ruta = {
    'nombre': 'Ruta de los Molinos',
    'coordenadas': {'latitud': 43.245, 'longitud': -5.721}
}

crear_kml(ruta)

