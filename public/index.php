<?php
$config = [
    'storage_directory' => __DIR__ . '/files/',
    'max_size' => 5000000,
    'db_dsn' => 'mysql:host=localhost;dbname=vidox',
    'db_user' => 'root',
    'db_pass' => 'root'
];

try {
    $db = new \PDO($config['db_dsn'], $config['db_user'], $config['db_pass']);
} catch (PDOException $e) {
    die(
        'Connect Error (' . $e->getCode() . ') '
        . $e->getMessage()
    );
}

$mode_id = str_replace('/', '', $_SERVER['REQUEST_URI']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vidox</title>
    <link rel="stylesheet" href="https://bootswatch.com/cyborg/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style type="text/css">
        .big-search {
            padding-bottom: 0;
            border: transparent;
            border-bottom: 1px solid #008F0D;
            background: transparent;
            height: 40px;
            color: white;
            line-height: 45px;
            font-size: 40px;
            text-align: center;
            width: 100%;
            outline: none;
            max-width: 800px;
        }

        label.uploadCover {
            display: block;
            cursor: pointer;
        }

        label.uploadCover input[type="file"] {
            opacity: 0;
            cursor: pointer;
        }

        .main-vid {
            max-width: 100%;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#main-nav-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">vidox</a>
        </div>

        <div class="collapse navbar-collapse" id="main-nav-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/upload"><span class="glyphicon glyphicon-upload"></span> Upload</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="text-center">
        <?php
        if ($mode_id == 'upload') {
            if (isset($_FILES['upload'])) {
                $new_id = uniqid();
                $target_file = $config['storage_directory'] . $new_id;
                $tFile = $config['storage_directory'] . basename($_FILES['upload']['name']);
                $fType = pathinfo($tFile, PATHINFO_EXTENSION);
                $target_file = $target_file . '.' . $fType;

                // Check file exists
                if (file_exists($target_file)) {
                    die('Sorry, file already exists.');
                }

                // Check size is less than specified size
                if ($_FILES['upload']['size'] > $config['max_size']) {
                    die('Sorry, your file is too large.');
                }

                if ($fType != 'mp4' && $fType != 'mkv') {
                    die('Sorry, only MP4 and MKV files are allowed.');
                }
                if (move_uploaded_file($_FILES['upload']['tmp_name'], $target_file)) {
                    if (!($stmt = $db->prepare('INSERT INTO `videos` (`hash`, `type`, `added`) VALUES (:hash, :type, :added)'))) {
                        echo 'Prepare failed: (' . $db->errorCode() . ') ' . $db->error;
                    }

                    $dateNow = date('Y-m-d H:i:s');

                    $stmt->bindParam(':hash', $new_id, \PDO::PARAM_STR);
                    $stmt->bindParam(':type', $fType, \PDO::PARAM_STR);
                    $stmt->bindParam(':added', $dateNow, \PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        die('<span>Successfully uploaded video with ID `<a href="/' . $new_id . '">' . $new_id . '</a>`');
                    }
                }

                die('Sorry, there was an error uploading your file.');
            } else {
                ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="uploadInput" class="uploadCover btn btn-default">
                            <input class="uploadinput" type="file" style=" height: 0; width: 0;" name="upload"
                                   id="uploadInput">
                            <span><span class="glyphicon glyphicon-upload"></span> Select Video</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-default" value="Upload"/>
                    </div>
                </form>
                <?php
            }
        } elseif ($mode_id !== '') {
            ?>
            <?php
            if (!($stmt = $db->prepare('SELECT * FROM `videos` WHERE `hash` = :hash'))) {
                echo 'Prepare failed: (' . $db->errorCode() . ') ' . $db->error;
            }
            if (!$stmt->bindParam(':hash', $mode_id, \PDO::PARAM_STR)) {
                echo 'Binding parameters failed: (' . $stmt->errorCode() . ') ' . $stmt->errorInfo()[2];
            }
            if (!$stmt->execute()) {
                echo 'Execute failed: (' . $stmt->errorCode() . ') ' . $stmt->errorInfo()[2];
            }

            if ($video = $stmt->fetch(\PDO::FETCH_OBJ)) {
                if ($stmt !== null && file_exists($config['storage_directory'] . $video->hash . '.' . $video->type)) {
                    ?>
                    <video class="main-vid" controls>
                        <source src="/files/<?php echo $video->hash . '.' . $video->type ?>" type="video/<?php echo $video->type ?>">
                        <p>Your browser does not support <?php echo $video->type ?>.</p>
                    </video>
                    <?php
                }
            } else {
                ?>
                <div class="text-center">
                    <h1>404</h1>
                    <h2>This video's ID could not be found.</h2>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="container well">
                <input title="Search" id="id" type="text" name="id" class="big-search" autocomplete="off"/>
                <script type="text/javascript">
                    $('#id').keypress(function () {
                        if (event.which == 13) {
                            var id = $(this).val();
                            window.location = "/" + id + "/";
                        }
                    });
                </script>
            </div>
            <?php
        }
        ?>
    </div>
</div>
</body>
</html>