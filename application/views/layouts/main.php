<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>
<?php $this->load->view('layouts/sidebar'); ?>
<?php $this->load->view('layouts/content_header'); ?>
<?php $this->load->view($content_view); ?>
<?php $this->load->view('layouts/footer'); ?>
<?php $this->load->view('layouts/scripts'); ?>
