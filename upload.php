<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>upload / AJ</title>

    <link rel="stylesheet" type="text/css" href="assets/css/main.css">

    <style>
        input { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div id="container">
        <?php
        require_once "config.php";

        if(CFG_FILE_MODIFY_ALLOWED) {
            $total = count($_FILES['upload']['name']);
            $ups = 0;
            $fails = 0;
            $errors = array();
            // Loop through each file
            for( $i=0 ; $i < $total ; $i++ ) {
            
            //Get the temp file path
            $tmpFilePath = $_FILES['upload']['tmp_name'][$i];
            
            //Make sure we have a file path
            if ($tmpFilePath != ""){
                //Setup our new file path
                $newFilePath = "./maps/" . $_FILES['upload']['name'][$i];
                if (file_exists($newFilePath)) {
                    $errors[] = "Failed to upload file: $newFilePath. File with same name already exists";
                    continue;
                }

                $parts = explode(".", $_FILES['upload']['name'][$i]);
                $ext = strtolower(end($parts));
                if ($ext == "png" || $ext == "jpg" || $ext == "gif") {
                    //Upload the file into the temp dir
                    if(move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $ups++;
                    } else {
                        $errors[] = "Failed to move file: $newFilePath";
                    }
                } else {
                    $errors[] = "Unsupported file extension \"$ext\" for file " . $_FILES['upload']['name'][$i];
                }
            }
            }

            if (sizeof($errors) > 0) {
                echo "<h3>Upload erros</h3>";
                echo "<pre>".implode("\n", $errors)."</pre>";
                echo "<hr class=\"alt\">";
            }

            if ($ups > 0 || sizeof($errors) > 0) {
                echo "<h3>Upload status</h3>";
                echo $ups . " files uploaded.<br>";
                echo sizeof($errors) . " failed.<br>";
                echo "<hr class=\"alt\">";
            }
        
        ?>
        <a href="index.php">Check status</a><br><br>
        
        <h3>Upload image files</h3>
        <small>Suppoerted formats: jpg, png, gif</small>
        <hr class="alt">
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input name="upload[]" type="file" multiple="multiple"><br>
            <input type="submit" value="Upload" name="submit">
        </form>
        <?php } else { ?>
        <h3>File upload is disabled</h3>
        <?php } ?>
    </div>
</body>
</html>