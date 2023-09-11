<form action="" method="post" enctype="multipart/form-data">
    <div class="row">
        <input type="file" name="image" required>
        <input type="submit" name="submit" value="Upload">
    </div>
</form>

<?php
define('KB', 1024);
define('MB', 1048576);


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//echo exec('whoami');


class OneImage
{
    public $file_size = 0;
    public $file_extension = "blank";
    public $width = 0;
    public $length = 0;
    public $fileName = "";

    public $file_tmpName = "";
}

class ImageProcessor
{
    public $targetDir = "";
    public $allowed_types = array("jpg", "jpeg", "gif", "png", "webp");
    
    public function initialize($target_directory){
        $this->targetDir = $target_directory;
    }

    public function get_one_image(){
        $out = new OneImage;
        $out->fileName = $_FILES["image"]["name"];
        $out->file_extension = strtolower(pathinfo($out->fileName, PATHINFO_EXTENSION));
        $out->file_tmpName = $_FILES["image"]["tmp_name"];
        $out->file_size = $_FILES["image"]["size"];

        //echo $out->file_extension;
        return $out;

    }

    public function image_passport($image){
        $out = false;
        
        //check extension
        $extension_okay = in_array($image->file_extension, $this->allowed_types);
        $size_okay = $image->file_size < (4*MB);
        printf("extension status: %d, size status: %d\n", $extension_okay, $size_okay);
        $out = $out || ($extension_okay && $size_okay);

        return $out;
    } 

    public function save_and_transform($image){
        //echo $image->file_tmpName . "<br>"; 
        if(move_uploaded_file($image->file_tmpName, $this->targetDir.$image->fileName)){
            $img_fd = NULL;
            if($image->file_extension == "png")
            {$img_fd = imagecreatefrompng($this->targetDir.$image->fileName);}
            else if($image->file_extension == "jpg" || $image->file_extension == "jpeg")
            {$img_fd = imagecreatefromjpeg($this->targetDir.$image->fileName);}
            else if($image->file_extension == "gif")
            {$img_fd = imagecreatefromgif($this->targetDir.$image->fileName);
             //imagepalettetotruecolor($img_fd);
             $exec_return = [];
             $exec_sourceFileName = $this->targetDir.$image->fileName;
             $exec_targetFileName = $this->targetDir . pathinfo($image->fileName, PATHINFO_FILENAME) . ".webp"; 
             
             exec("gif2webp -q 50 {$exec_sourceFileName} -o {$exec_targetFileName}", $exec_return);
             print_r($exec_return);

             return;
            }
            else if($image->file_extension == "webp")
            {return;}


            //convert to webp
            imagewebp($img_fd, $this->targetDir . pathinfo($image->fileName, PATHINFO_FILENAME)
                                                 . ".webp");
            //delete legacy images
            imagedestroy($img_fd);
            unlink($this->targetDir.$image->fileName);
            
        }
        else{
            echo "image save failed due to error:" . $_FILES["image"]["error"] . "\n";
        }
    }

    public function process_image(){
        $image = $this->get_one_image();

        if($this->image_passport($image)){
            $this->save_and_transform($image);
        }


    }

}


//if receive from client
if(isset($_POST["submit"])){
    //if file has image
    if(isset($_FILES["image"])){
                /*
                $targetDir = "/var/www/html/uploads/";
                $targetFile = $targetDir . basename($_FILES["image"]["name"]);
                $uploadOk = 1;
                $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                
                echo "$targetFile\n";

                if(move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)){
                    echo "File upload sucess";
                } else {
                    echo "upload failed";
                }
                */
                $imageProcessor_obj = new ImageProcessor;
                $imageProcessor_obj->initialize("/var/www/html/uploads/");
                $imageProcessor_obj->process_image();

    }
    
}


?>