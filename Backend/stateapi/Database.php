<?php
interface Database
{
    public function initialize();
    public function getStates();
    public function getCapital($stateName);
    public function search($stateName);
    public function getByZone($zone);
    public function getByMineral($mineral);
}
