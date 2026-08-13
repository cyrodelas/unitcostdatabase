<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($this->session->flashdata('reference_success')): ?><div class="alert alert-success" role="alert"><?= html_escape($this->session->flashdata('reference_success')) ?></div><?php endif; ?>
<div class="card shadow-sm">
	<div class="card-header d-flex align-items-center">
		<h2 class="card-title mb-0"><?= html_escape($entity['title']) ?></h2>
		<?php if ($can_manage): ?><a class="btn btn-primary btn-sm ms-auto" href="<?= site_url('references/'.$type.'/create') ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add Record</a><?php endif; ?>
	</div>
	<div class="card-body">
		<?php if($is_paginated):?><form class="row g-2 align-items-center mb-3" method="get" action="<?=site_url('references/'.$type)?>"><div class="col-sm-8 col-lg-5"><label class="visually-hidden" for="reference-search">Search</label><input class="form-control" id="reference-search" name="q" value="<?=html_escape($search)?>" placeholder="Search reference records"></div><div class="col-auto"><button class="btn btn-outline-primary" type="submit">Search</button></div><?php if($search!==''):?><div class="col-auto"><a class="btn btn-outline-secondary" href="<?=site_url('references/'.$type)?>">Clear</a></div><?php endif;?></form><p class="small text-body-secondary"><?=number_format($total_records)?> matching record<?=($total_records===1?'':'s')?>; showing up to 100 per page.</p><?php endif;?>
		<div class="table-responsive">
			<table <?=$is_paginated?'':'id="reference-table"'?> class="table table-striped table-hover align-middle w-100">
				<thead><tr>
					<?php foreach ($entity['fields'] as $field): ?><th><?= html_escape($field['label']) ?></th><?php endforeach; ?>
					<th>Status</th><?php if($entity['timestamps'] ?? TRUE):?><th>Updated</th><?php endif;?><?php if ($can_manage): ?><th data-dt-order="disable">Actions</th><?php endif; ?>
				</tr></thead>
				<tbody>
				<?php foreach ($records as $record): ?>
					<tr>
						<?php foreach ($entity['fields'] as $name => $field): ?>
							<?php $value = $field['type'] === 'lookup' ? ($record->{$name.'_display'} ?? '') : ($record->{$name} ?? ''); ?>
							<td><?= $field['type'] === 'date' && $value ? html_escape(date('d-M-Y', strtotime($value))) : ($field['type']==='checkbox' ? ($value?'Yes':'No') : html_escape($value ?? '')) ?></td>
						<?php endforeach; ?>
						<td data-order="<?= (int) $record->is_active ?>"><span class="badge text-bg-<?= $record->is_active ? 'success' : 'secondary' ?>"><?= $record->is_active ? 'Active' : 'Inactive' ?></span></td>
						<?php if($entity['timestamps'] ?? TRUE):?><td data-order="<?= html_escape($record->updated_at) ?>"><?= html_escape(date('d-M-Y h:i A', strtotime($record->updated_at))) ?></td><?php endif;?>
						<?php if ($can_manage): ?><td class="text-nowrap">
							<a class="btn btn-outline-primary btn-sm" href="<?= site_url('references/'.$type.'/'.$record->{$entity['primary_key']}.'/edit') ?>" aria-label="Edit"><i class="bi bi-pencil" aria-hidden="true"></i></a>
							<?= form_open('references/'.$type.'/'.$record->{$entity['primary_key']}.'/status', array('class' => 'd-inline')) ?><button class="btn btn-outline-<?= $record->is_active ? 'danger' : 'success' ?> btn-sm" type="submit" aria-label="<?= $record->is_active ? 'Deactivate' : 'Activate' ?>"><i class="bi bi-<?= $record->is_active ? 'pause-circle' : 'play-circle' ?>" aria-hidden="true"></i></button><?= form_close() ?>
						</td><?php endif; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php if($is_paginated&&$total_pages>1):?><?php $base=site_url('references/'.$type).'?q='.rawurlencode($search).'&page=';?><nav aria-label="Reference pages"><ul class="pagination mb-0"><li class="page-item <?=$page<=1?'disabled':''?>"><a class="page-link" href="<?=$page<=1?'#':$base.($page-1)?>">Previous</a></li><li class="page-item disabled"><span class="page-link">Page <?=number_format($page)?> of <?=number_format($total_pages)?></span></li><li class="page-item <?=$page>=$total_pages?'disabled':''?>"><a class="page-link" href="<?=$page>=$total_pages?'#':$base.($page+1)?>">Next</a></li></ul></nav><?php endif;?>
	</div>
</div>
