# Integrates a three.js Viewer Into Your Omeka Project
A 3D viewer geared towards cultural heritage collections. Includes tools for measurement, dynamic lighting, interactive shaders and materials settings.

Currently supports [OBJ + MTL](https://en.wikipedia.org/wiki/Wavefront_.obj_file) files and a variety of texture formats (no EXR yet, sorry), as well as [PTM](http://www.hpl.hp.com/research/ptm/downloads/PtmFormat12.pdf) files for projects doing [Reflectance Transformation Imaging](http://culturalheritageimaging.org/Technologies/RTI/). Support for more formats is planned for the future.

Still in active development, but it's been tested and should work &#8482;.

## Installation
Clone this repository into your plugins directory

`git clone https://github.com/rochester-rcl/plugin-ThreeJS.git ThreeJS`

Install it like you would install any other Omeka plugin.

## Usage
To prepare a file for upload, you can use one of our conversion tools:

For [OBJ + MTL meshes](http://dslab.digitalscholar.rochester.edu/threejs-tools/converter)

For [PTM files](http://dslab.digitalscholar.rochester.edu/threejs-tools/ptm-converter)

##### NOTE
These are browser-based conversion tools, so performance is based on the machine they're being run on. Chrome is recommended. They will also be bundled with the next release of the plugin once a few more features are added.

### Preparing Meshes

##### Mesh Converter Instructions

Mesh -- the mesh input files (OBJ and MTL)

Maps -- where you can upload all of your PBR maps. [Click here](https://threejs.org/docs/#api/en/materials/MeshStandardMaterial) for the full details on supported maps

##### Mesh Converter Options

| Option                           | Description                                                                                                                       |
|----------------------------------|-----------------------------------------------------------------------------------------------------------------------------------|
| Re-Center Geometry (recommended) | Centers the model in the viewer                                                                                                   |
| Use JPEG Compression ...         | Any maps > 2048x2048 are transcoded to jpegs regardless of their original format                                                  |
| Use zlib Compression ...         | Compresses the mesh and texture data using the deflate algorithm. Results in a .gz file. If unchecked, the output is a JSON file  |
| Normal Map from Diffuse          | A normal map is estimated based on the horizontal and vertical gradients of the diffuse texture. Can help to add detail to simplified meshes |

Once you run the conversion (which may be quite slow depending on the options selected), you can modify the name of the output file and save it.

[image goes here]

##### PTM Converter Instructions

The PTM converter works pretty much the same as the above, with the exception of some post-processing options.

##### PTM Converter Options

| Option                           | Description                                                                                                                                                |
|----------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Create Mesh from Surface Normals | Attempts to estimate depth from the surface normals extracted from the PTM. Produces a surface reconstruction as opposed to a flat plane. Results may vary |
| Smooth Normal Map                | Sometimes the normals from this process are noisy. This option applies a median filter to smooth out the normal map                                        |
| Use JPEG Compression ...         | Any maps > 2048x2048 are transcoded to jpegs regardless of their original format                                                                           |
| Use zlib Compression ...         | Compresses the mesh and texture data using the deflate algorithm. Results in a .gz file. If unchecked, the output is a JSON file                           |




##### NOTE
Some optimization attempts have been made with the conversion tools, and all processing is done in Web Workers wherever possible, but you may run into memory issues if you attempt to create normal maps from very large textures (>=8192x8192) on a below-average machine. Similarly, you may run into issues with very large PTM files (>300MB), and processing may take a few minutes.

## Adding Meshes to an Omeka Project
