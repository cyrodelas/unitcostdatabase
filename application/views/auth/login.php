<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Sign in | Project Nexus UCD</title>
	<link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap-icons/font/bootstrap-icons.min.css') ?>">
	<link rel="stylesheet" href="<?= base_url('assets/adminlte/css/adminlte.min.css') ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/ucd.css') ?>">
</head>
<body class="login-page bg-body-secondary">
	<div class="login-box">
		<div class="card card-outline card-primary shadow">
			<div class="card-header text-center py-4">
				<span class="brand-mark" aria-hidden="true">N</span>
				<h1 class="h4 d-inline align-middle ms-1">Project Nexus UCD</h1>
			</div>
			<div class="card-body login-card-body">
				<p class="login-box-msg">Sign in to continue</p>
				<?php if ($this->session->flashdata('auth_error')): ?>
					<div class="alert alert-danger" role="alert"><?= html_escape($this->session->flashdata('auth_error')) ?></div>
				<?php endif; ?>
				<?= validation_errors('<div class="alert alert-danger" role="alert">', '</div>') ?>
				<?= form_open('login', array('novalidate' => 'novalidate')) ?>
					<div class="input-group mb-3">
						<input type="text" name="identity" class="form-control" placeholder="Username or email" value="<?= set_value('identity') ?>" autocomplete="username" required autofocus>
						<div class="input-group-text"><span class="bi bi-person" aria-hidden="true"></span></div>
					</div>
					<div class="input-group mb-3">
						<input type="password" name="password" class="form-control" placeholder="Password" autocomplete="current-password" required>
						<div class="input-group-text"><span class="bi bi-lock-fill" aria-hidden="true"></span></div>
					</div>
					<button type="submit" class="btn btn-primary w-100">Sign In</button>
				<?= form_close() ?>
			</div>
		</div>
		<p class="text-center text-body-secondary small mt-3">Unit Cost Database Web-Based System</p>
	</div>
	<script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.min.js') ?>"></script>
</body>
</html>
