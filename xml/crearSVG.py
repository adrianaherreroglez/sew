import svgwrite

def crear_svg(ruta):
    dwg = svgwrite.Drawing('ruta1.svg', profile='tiny')

    # Crear una línea de ejemplo para el perfil de altimetría
    dwg.add(dwg.line(start=(0, 100), end=(200, 80), stroke=svgwrite.rgb(0, 0, 0, '%')))

    # Guardar el archivo SVG
    dwg.save()

crear_svg('ruta1')
