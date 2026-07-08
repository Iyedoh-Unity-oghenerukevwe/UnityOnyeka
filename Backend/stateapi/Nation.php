<?php
include "Database.php";

class Nation implements Database
{
    private $data;

    public function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        include "api.php";
        $this->data = $states;
    }

    public function getStates()
    {
        return $this->data;
    }

    public function getCapital($stateName)
    {
        foreach ($this->data as $state) {
            if (strtolower($state['State_Name']) === strtolower($stateName)) {
                return [
                    "State" => $state['State_Name'],
                    "Capital" => $state['capital']
                ];
            }
        }
        return ["error" => "State not found"];
    }

    public function search($stateName)
    {
        foreach ($this->data as $state) {
            if (strtolower($state['State_Name']) === strtolower($stateName)) {
                return $state;
            }
        }
        return ["error" => "State not found"];
    }

    public function getByZone($zone)
    {
        $result = [];
        foreach ($this->data as $state) {
            if (strtolower($state['Geopolitical_Zone']) === strtolower($zone)) {
                $result[] = $state;
            }
        }
        if (count($result) > 0) {
            return $result;
        }
        return ["error" => "Zone not found"];
    }

    public function getByMineral($mineral)
    {
        $result = [];
        foreach ($this->data as $state) {
            if (stripos($state['Mineral_Resources'], $mineral) !== false) {
                $result[] = $state;
            }
        }
        if (count($result) > 0) {
            return $result;
        }
        return ["error" => "No state found with that mineral"];
    }
}
