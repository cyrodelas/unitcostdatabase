<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row justify-content-center">
	<div class="col-xl-8">
		<div class="card shadow-sm">
			<div class="card-header"><h2 class="card-title mb-0"><?= html_escape($user->display_name) ?></h2></div>
			<div class="card-body">
				<?php if ($form_error): ?><div class="alert alert-danger" role="alert"><?= html_escape($form_error) ?></div><?php endif; ?>
				<p class="text-body-secondary">Select one or more active roles for <strong><?= html_escape($user->username) ?></strong>.</p>
				<?= form_open(current_url()) ?>
					<div class="row g-3">
						<?php foreach ($roles as $role): ?>
							<div class="col-md-6">
								<div class="form-check border rounded p-3 ps-5 h-100">
									<input class="form-check-input" type="checkbox" name="role_ids[]" value="<?= (int) $role->role_id ?>" id="role_<?= (int) $role->role_id ?>" <?= in_array((int) $role->role_id, $selected_role_ids, TRUE) ? 'checked' : '' ?>>
									<label class="form-check-label" for="role_<?= (int) $role->role_id ?>"><strong><?= html_escape($role->role_name) ?></strong><span class="d-block small text-body-secondary"><?= html_escape($role->description ?: $role->role_code) ?></span></label>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="mt-4"><button class="btn btn-primary" type="submit">Save Role Assignments</button> <a class="btn btn-outline-secondary" href="<?= site_url('user-roles') ?>">Cancel</a></div>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
