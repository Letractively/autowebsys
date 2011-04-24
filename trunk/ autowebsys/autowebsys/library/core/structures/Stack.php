<?php
class Stack {
    private $array;
    private $index;

    public function __construct() {
        $this->array = array();
        $this->index = -1;
    }

    public function push($element) {
        $this->array[++$this->index] = $element;
    }

    public function pop() {
        return $this->array[$this->index--];
    }

    public function whatsOnTop() {
        return $this->array[$this->index];
    }

    public function isEmpty() {
        return $this->index < 0;
    }

}

?>
