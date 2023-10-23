<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');

include_once('./Database.php');

$plainText = generateRandomString(32);

echo "Plain Text : $plainText <br>";

echo findRHOinBabyHash($plainText)."<br>";

//echo findCollision();


function findRHOMD5($plain,$cycle=1)
{
    $db = array('dbname'=>'test_collision','host'=>'localhost','user'=>'root','pass'=>'root');
    $com = new Database($db);
    $md5 = md5($plain);

    $chek = $com->callSql("SELECT id FROM rho_attack WHERE hash='$md5'",'value');

    if(empty($chek))
    {
        $quarter_text = substr($md5,0,8); // 8 Bytes
        $quarter_count = $com->callSql("SELECT COUNT(id) FROM rho_attack WHERE hash LIKE '$quarter_text%'",'value');

        $half_text = substr($md5,0,16); // 8 Bytes
        $half_count = $com->callSql("SELECT COUNT(id) FROM rho_attack WHERE hash LIKE '$half_text%'",'value');

        $com->callSql("INSERT INTO rho_attack (`password`,`hash`,`quarter`,`half`) VALUES ('$plain','$md5',$quarter_count,$half_count)");

        $cycle++;

        if($cycle == 1000  )
        {
            die('Stop');
        }

        return findRHOMD5($md5,$cycle);
    }

    return $md5;
}


function findRHOinBabyHash($plain,$array=array())
{
    $hash = babyHash($plain);

    if(in_array($hash,$array))
    {
        $array[] = $hash;
        $count = count($array);
        echo " Collision Hash : $hash  <br>";
        echo 'Ended Collision at ' .$count ;

        exit();
    }
    $array[] = $hash;
    return findRHOinBabyHash($hash,$array);
}

function findCollision()
{
    $intialText = generateRandomString(32);

    $hash =  babyHash($intialText);

    $array[$intialText] = array($hash);

    $count = 1;

    while (true)
    {
        $randomText = generateRandomString(32);

        $new_hash = babyHash($randomText);

        if(!array_key_exists($randomText,$array))
        {
            $count++;
            if(in_array($new_hash,$array))
            {
                $collision_word = array_search($new_hash,$array);
                echo "$randomText --- $new_hash ----$collision_word<br>";
                die('Collision Found '. $count);
            }

            $array[$randomText] = $new_hash;
        }
    }
}


function babyHash($input) {
    $hash = 0;
    $multiplier = 1;

    for ($i = 0; $i < strlen($input); $i++) {
        $charCode = ord($input[$i]);
        $hash = ($hash + ($charCode * $multiplier)) % 100000000;
        $multiplier = ($multiplier * 31) % 100000000; 
    }

    $hash = str_pad($hash, 8, '0', STR_PAD_RIGHT); 

    $hash = dechex($hash);

    $hash = str_pad($hash,8,'0',STR_PAD_RIGHT);

   return $hash;
}

function generateRandomString($length = 32) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

?>
