<?php

class DB
{
    public function connect()
        {
            $conn=new PDO('mysql:host=213.171.200.36;dbname=faziz','fahimaziz',password:'Password20*');
            $conn->setAttribute(attribute:PDO::ATTR_ERRMODE, value:PDO::ERRMODE_EXCEPTION);
 
            return $conn;
        }



}