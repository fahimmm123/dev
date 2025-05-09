<?php

    class Product
    {
        private $id;
        private $name;
        private $image;

        public function __construct($id, $name, $image)
        {
            $this->id = $id;
            $this->name = $name;
            $this->image = $image;
        }

        public function id()
        {
            return $this->id;
        }
        
        public function name()
        {
            return $this->name;
        }

        public function image()
        {
            return $this->image;
        }
    }