<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="color-scheme" content="light dark">
	<title><?= html_escape($page_title ?? 'Project Nexus UCD') ?> | Project Nexus UCD</title>
	<script src="<?= base_url('assets/js/theme-preload.js') ?>"></script>
	<link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap-icons/font/bootstrap-icons.min.css') ?>">
	<link rel="stylesheet" href="<?= base_url('assets/plugins/overlayscrollbars/css/overlayscrollbars.min.css') ?>">
	<link rel="stylesheet" href="<?= base_url('assets/adminlte/css/adminlte.min.css') ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/ucd.css') ?>">
	<?php foreach (($page_styles ?? array()) as $page_style): ?>
		<link rel="stylesheet" href="<?= base_url($page_style) ?>">
	<?php endforeach; ?>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary">
<div class="app-wrapper">
