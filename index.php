<?php
  $dbDetails = (object) [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'vidox',
  ];

  $db = new mysqli($dbDetails->host, $dbDetails->user, $dbDetails->pass, $dbDetails->name);
  if ($db->connect_error) {
      die('Connect Error ('.$db->connect_errno.') '
            .$db->connect_error);
  }
  $mode_id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
 ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vidox</title>
	<link rel="stylesheet" href="https://bootswatch.com/cyborg/bootstrap.min.css" integrity="sha384-MnR/tAdMR2vYfROXmBldczUJ7JqlT7aXOo8b86EdVzdnYU0sJ+0fdXdwmFA5qosE" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  <style type="text/css">
  .big-search {
    padding-bottom: 0px;
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
  </style>
  </head>
  <body><nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-nav-collapse" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/">vidox</a>
    </div>

    <div class="collapse navbar-collapse" id="main-nav-collapse">
      <ul class="nav navbar-nav navbar-right">
        <li><a href="/upload/"><span class="glyphicon glyphicon-upload"></span> Upload</a></li>
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
              $target_dir = 'files/';
              $target_file = $target_dir.$new_id;
              $tFile = $target_dir.basename($_FILES['upload']['name']);
              $fType = pathinfo($tFile, PATHINFO_EXTENSION);
              $target_file = $target_file.'.'.$fType;
              if (file_exists($target_file)) {
                  echo 'Sorry, file already exists. ';
                  exit();
              }
              if ($_FILES['upload']['size'] > 500000) {
                  echo 'Sorry, your file is too large. ';
                  exit();
              }
              if ($fType != 'mp4' || $fType != 'mkv') {
                  echo 'Sorry, only MP4 and MKV files are allowed. ';
                  exit();
              }
              if (move_uploaded_file($_FILES['upload']['tmp_name'], $target_file)) {
                  if (!($stmt = $db->prepare('INSERT INTO `videos` (`hash`, `type`, `added`) VALUES (?, ?, ?)'))) {
                      echo 'Prepare failed: ('.$db->errno.') '.$db->error;
                  }
                  if (!$stmt->bind_param('sss', $new_id, $fType, date('Y-m-d H:i:s'))) {
                      echo 'Binding parameters failed: ('.$stmt->errno.') '.$stmt->error;
                  }
                  if (!$stmt->execute()) {
                      echo 'Execute failed: ('.$stmt->errno.') '.$stmt->error;
                  }
              } else {
                  echo 'Sorry, there was an error uploading your file. ';
              }
          } else {
              ?>
            <form action="" method="POST" enctype="multipart/form-data">
              <div class="form-group">
                <label for="uploadInput" class="uploadCover btn btn-default">
                  <input class="uploadinput" type="file" style=" height: 0px; width: 0px;" name="upload" id="uploadInput">
                  <span><span class="glyphicon glyphicon-upload"></span> Select Video</span>
                </label>
              </div>
              <div class="form-group">
                <input type="submit" class="btn btn-default" value="Upload"/>
              </div>
            </form>
          <?php

          }
          ?>
      <?php

      } elseif ($mode_id !== '') {
          ?>
      <?php
          if (!($stmt = $db->prepare('SELECT * FROM `videos` WHERE `hash`=?'))) {
              echo 'Prepare failed: ('.$db->errno.') '.$db->error;
          }
          if (!$stmt->bind_param('s', $mode_id)) {
              echo 'Binding parameters failed: ('.$stmt->errno.') '.$stmt->error;
          }
          if (!$stmt->execute()) {
              echo 'Execute failed: ('.$stmt->errno.') '.$stmt->error;
          }
          $stmt->bind_result($id, $hash, $type, $added, $is_deleted);
          while ($stmt !== null && $stmt->fetch()) {
              if ($id !== null && file_exists('./files\/'.$hash.'.'.$type)) {
                  ?>
            <video controls>
              <source src="/files/<?=$hash.'.'.$type?>" type="video/<?=$type?>">
              <p>Your browser does not support <?=$type?>.</p>
            </video>
        <?php

              }
              exit();
          }
              ?>
  <div class="text-center">
    <h1>404</h1>
    <h2>This video's ID could not be found.</h2>
  </div>
  <?php
      } else {
          ?>
        <div class="container well">
          <input id="id" type="text" name="id" class="big-search" autocomplete="off"/>
          <script type="text/javascript">
            $('#id').keypress(function() {
              if ( event.which == 13 ) {
                 var id = $(this).val();
                 window.location = "/" + id + "/";
              }
            });
          </script>
        </div>
      <?php

      } ?>
    </div>
	</div>
  </body>
</html>
<?php $db->close(); ?>