<?php

if (array_key_exists('pi_error', $_SESSION) && array_key_exists('error', $_SESSION['pi_error'])) {
    pq('.box_info')->text($_SESSION['pi_error']['error']);
    pq('.box_info')->addClass('box_error');
    pq('.box_error')->attr('style', 'border:2px solid red');
    pq('.box_info')->removeClass('box_info');
    unset($_SESSION['pi_error']['error']);
}
