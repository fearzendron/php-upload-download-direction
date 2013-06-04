<?php


    $path_de_upload = "upload/";
    
    if ($_FILES["file"]["error"] > 0)
    {
        
        echo "Error: " . $_FILES["file"]["error"] . "<br />";
    
        
    } else {
        
        echo "Upload: " . $_FILES["file"]["name"] . "<br />";
        echo "Tipo: " . $_FILES["file"]["type"] . "<br />";
        echo "Tamanho: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
        echo "Armazenado: " . $_FILES["file"]["tmp_name"]."<br />";
        
        if (file_exists($path_de_upload . $_FILES["file"]["name"])) {
            echo $_FILES["file"]["name"] . " já existe. ";
        } else {
            move_uploaded_file($_FILES["file"]["tmp_name"],
            $path_de_upload . $_FILES["file"]["name"]);
            echo "Stored in: " . $path_de_upload . $_FILES["file"]["name"];
        }
        
    }
    

    $folder = opendir("upload/");
    $pic_types = array("jpg", "jpeg", "gif", "png");
    $index = array();

    while ($file = readdir ($folder)) {
        if(in_array(substr(strtolower($file), strrpos($file,".") + 1),$pic_types)) {
            array_push($index,$file);
            echo $file."<br />";
        }
    }

    echo "<br /><br />";
    
    
?>
