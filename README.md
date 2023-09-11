index.php holds two classes;

class: image
a class holds every important meta datas of a POSTed image, like image name, image size, image tmp name, etc.

class: image processor
Objects init from it are responsible for converting image files.

The object needs to be initialized with a string, which ddefines it's target disk location for storing images.

This object's proceduals of converting an image:

	on receive image form client:
		check image's size, and has an acceptable file format (footnote 1)
		if qualified:
			save the image from tempory domain to file system
			perform transforms base on file extensions, a newfile.webp is written.
			destroy the original image file.


footnote 1: currently it can only handle: png, gif, jpg, jpeg, and webp itself. TIFF and JSON are pretty hard to transform, since they are more like raw data rather than pictures. Heming found a lib called imagiTrick, which might be able to handle them, he has not look into it throughly yet.
