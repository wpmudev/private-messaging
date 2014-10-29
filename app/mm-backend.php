<?php

/**
 * Author: Hoang Ngo
 */
class MM_Backend
{
    public function __construct()
    {
        new MMessage_Backend_Controller();
    }
}