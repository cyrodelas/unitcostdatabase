<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row g-3 mb-4">
	<?php foreach ($cards as $card): ?>
		<div class="col-6 col-lg-3">
			<div class="small-box text-bg-<?= html_escape($card['color']) ?> h-100 mb-0">
				<div class="inner"><h3><?= number_format($card['value']) ?></h3><p><?= html_escape($card['label']) ?></p></div>
				<i class="small-box-icon bi <?= html_escape($card['icon']) ?>" aria-hidden="true"></i>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
	<div class="col-lg-5">
		<div class="card shadow-sm h-100">
			<div class="card-header"><h2 class="card-title mb-0">Current Revision Status</h2></div>
			<div class="card-body"><div class="dashboard-chart"><canvas id="revision-status-chart" aria-label="Current standard cost item revisions by status" role="img"></canvas></div></div>
		</div>
	</div>
	<div class="col-lg-7">
		<div class="card shadow-sm h-100">
			<div class="card-header"><h2 class="card-title mb-0">Resource Build-Up Coverage</h2></div>
			<div class="card-body"><div class="dashboard-chart"><canvas id="resource-coverage-chart" aria-label="Current revisions with material, labor, and equipment build-ups" role="img"></canvas></div></div>
		</div>
	</div>
</div>

<div class="card shadow-sm">
	<div class="card-header"><h2 class="card-title mb-0">Operational Snapshot</h2></div>
	<div class="card-body">
		<div class="row g-4 text-center">
			<div class="col-6 col-lg-3"><div class="snapshot-value"><?= number_format($operational_snapshot['coded_current_revisions']) ?></div><div class="text-body-secondary">Coded current revisions</div></div>
			<div class="col-6 col-lg-3"><div class="snapshot-value"><?= number_format($operational_snapshot['rate_observations']) ?></div><div class="text-body-secondary">Rate observations</div></div>
			<div class="col-6 col-lg-3"><div class="snapshot-value"><?= number_format($operational_snapshot['validated_rate_observations']) ?></div><div class="text-body-secondary">Validated observations</div></div>
			<div class="col-6 col-lg-3"><div class="snapshot-value"><?= number_format($operational_snapshot['projects']) ?></div><div class="text-body-secondary">Active projects</div></div>
		</div>
	</div>
</div>

<div id="dashboard-data" data-chart="<?= html_escape(json_encode($chart_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>" hidden></div>
