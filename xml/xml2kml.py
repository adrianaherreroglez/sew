def extraer_coordenadas_de_xml(archivoXML):
    try:
        tree = ET.parse(archivoXML)
    except (IOError, ET.ParseError) as e:
        print(f"Error al procesar el archivo XML: {e}")
        exit()

    root = tree.getroot()
    coordenadas = []

    for hito in root.findall(".//hito"):
        long_elem = hito.find("./coordenadas/longitud")
        lat_elem = hito.find("./coordenadas/latitud")
        alt_elem = hito.find("./coordenadas/altitud")

        if long_elem is not None and lat_elem is not None and alt_elem is not None:
            long = long_elem.text.strip()
            lat = lat_elem.text.strip()
            alt = alt_elem.text.strip()
            coordenadas.append(f"{long},{lat},{alt}")

    if coordenadas:
        coordenadas.append(coordenadas[0])

    return "\n".join(coordenadas)
