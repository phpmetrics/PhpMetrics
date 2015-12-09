<?php
class ClassWhoUseAnother {
    public function demo()
    {
        return new ClassWhoIsUsed;
    }
}