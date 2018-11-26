<?php
foreach ($_SERVER as $key => $value){
    if(strpos($key,'HTTP_') === 0){
        echo $key.':'.$value.'<br>';
    }
}

