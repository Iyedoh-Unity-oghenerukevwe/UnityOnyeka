<?php

include "Nation.php";

$Nigeria = new Nation();

$action = $_GET['action'] ?? 'all';
$state = $_GET['state'] ?? '';
$zone = $_GET['zone'] ?? '';
$mineral = $_GET['mineral'] ?? '';

if ($action === 'all') {
    echo json_encode($Nigeria->getStates());
} elseif ($action === 'capital' && $state !== '') {
    echo json_encode($Nigeria->getCapital($state));
} elseif ($action === 'search' && $state !== '') {
    echo json_encode($Nigeria->search($state));
} elseif ($action === 'zone' && $zone !== '') {
    echo json_encode($Nigeria->getByZone($zone));
} elseif ($action === 'minerals' && $mineral !== '') {
    echo json_encode($Nigeria->getByMineral($mineral));
} else {
    echo json_encode(["error" => "Invalid request"]);
}
