<?php

function setMessage($type, $message){
    $_SESSION['message'] = [
        'type' => $type,
        'text' =>$message
    ];
}
function showMessage(){
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message']['type'];
        $text= $_SESSION['message']['text'];
        echo "<div class='alert alert-$type'>$text</div>";
        unset($_SESSION['message']);
    }
}

