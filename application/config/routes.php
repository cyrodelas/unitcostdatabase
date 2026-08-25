<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'dashboard';
$route['dashboard'] = 'dashboard/index';
$route['materials'] = 'materials/index';
$route['materials/create'] = 'materials/create';
$route['materials/(:num)'] = 'materials/view/$1';
$route['materials/(:num)/edit'] = 'materials/edit/$1';
$route['materials/(:num)/status'] = 'materials/status/$1';
$route['equipment'] = 'equipment/index';
$route['equipment/create'] = 'equipment/create';
$route['equipment/(:num)'] = 'equipment/view/$1';
$route['equipment/(:num)/edit'] = 'equipment/edit/$1';
$route['equipment/(:num)/status'] = 'equipment/status/$1';
$route['labor'] = 'labor/index';
$route['labor/create'] = 'labor/create';
$route['labor/(:num)'] = 'labor/view/$1';
$route['labor/(:num)/edit'] = 'labor/edit/$1';
$route['labor/(:num)/status'] = 'labor/status/$1';
$route['crews'] = 'crews/index';
$route['crews/create'] = 'crews/create';
$route['crews/(:num)'] = 'crews/view/$1';
$route['crews/(:num)/edit'] = 'crews/edit/$1';
$route['crews/(:num)/status'] = 'crews/status/$1';
$route['crews/(:num)/members/create'] = 'crews/add_member/$1';
$route['crews/(:num)/members/(:num)/edit'] = 'crews/edit_member/$1/$2';
$route['standard-cost-items'] = 'standard_cost_items/index';
$route['standard-cost-items/create'] = 'standard_cost_items/create';
$route['standard-cost-items/(:num)'] = 'standard_cost_items/view/$1';
$route['standard-cost-items/(:num)/edit'] = 'standard_cost_items/edit/$1';
$route['standard-cost-items/(:num)/lifecycle'] = 'standard_cost_items/lifecycle/$1';
$route['unit-rates'] = 'unit_rates/index';
$route['unit-rates/(:num)'] = 'unit_rates/view/$1';
$route['unit-rates/(:num)/(material|labor|equipment|allowance)/create'] = 'unit_rates/add/$1/$2';
$route['unit-rates/(:num)/(material|labor|equipment|allowance)/(:num)/edit'] = 'unit_rates/edit/$1/$2/$3';
$route['unit-rates/(:num)/(material|labor|equipment|allowance)/(:num)/delete'] = 'unit_rates/delete/$1/$2/$3';
$route['unit-rates/(:num)/productivity/create'] = 'crew_productivity/create/$1';
$route['unit-rates/(:num)/productivity/(:num)/edit'] = 'crew_productivity/edit/$1/$2';
$route['unit-rates/(:num)/productivity/(:num)/apply'] = 'crew_productivity/apply/$1/$2';
$route['unit-rates/(:num)/labor/manual'] = 'crew_productivity/manual/$1';
$route['rates'] = 'rates/index';
$route['rates/(material|labor|cost_item)/create'] = 'rates/create/$1';
$route['elemental-costs'] = 'elemental_costs/index';
$route['elemental-costs/create'] = 'elemental_costs/create';
$route['elemental-costs/rates'] = 'elemental_costs/rates';
$route['elemental-costs/rates/create'] = 'elemental_costs/create_rate';
$route['elemental-costs/scope'] = 'elemental_costs/scope';
$route['elemental-costs/scope/create'] = 'elemental_costs/create_scope';
$route['elemental-costs/scope/(:num)/edit'] = 'elemental_costs/edit_scope/$1';
$route['elemental-costs/(:num)'] = 'elemental_costs/view/$1';
$route['elemental-costs/(:num)/edit'] = 'elemental_costs/edit/$1';
$route['elemental-costs/(:num)/elements/create'] = 'elemental_costs/add_element/$1';
$route['elemental-costs/(:num)/elements/(:num)/edit'] = 'elemental_costs/edit_element/$1/$2';
$route['elemental-costs/(:num)/action/(submit|return-draft|approve|publish|archive)'] = 'elemental_costs/action/$1/$2';
$route['governance/review'] = 'governance/review';
$route['governance/approval'] = 'governance/approval';
$route['governance/audit'] = 'governance/audit';
$route['governance/revisions/(:num)/(submit|recommend|return-draft|approve|return-review|publish|revise)'] = 'governance/action/$1/$2';
$route['projects'] = 'projects/index';
$route['projects/create'] = 'projects/create';
$route['projects/(:num)'] = 'projects/view/$1';
$route['projects/(:num)/edit'] = 'projects/edit/$1';
$route['projects/(:num)/status'] = 'projects/status/$1';
$route['projects/(:num)/delete'] = 'projects/delete/$1';
$route['boq'] = 'boq/index';
$route['boq/create'] = 'boq/create';
$route['boq/(:num)'] = 'boq/view/$1';
$route['boq/(:num)/edit'] = 'boq/edit/$1';
$route['boq/(:num)/status'] = 'boq/status/$1';
$route['boq/(:num)/delete'] = 'boq/delete/$1';
$route['boq/(:num)/items/create'] = 'boq/add_item/$1';
$route['boq/(:num)/items/(:num)/edit'] = 'boq/edit_item/$1/$2';
$route['boq/(:num)/items/(:num)/status'] = 'boq/item_status/$1/$2';
$route['boq/(:num)/import'] = 'boq/import/$1';
$route['boq/(:num)/imports/(:num)'] = 'boq/batch/$1/$2';
$route['boq/(:num)/imports/(:num)/predictions/(:num)/review'] = 'boq/review_extraction/$1/$2/$3';
$route['boq/(:num)/imports/(:num)/commit'] = 'boq/commit/$1/$2';
$route['boq-mapping'] = 'boq_mapping/index';
$route['boq-mapping/(:num)'] = 'boq_mapping/view/$1';
$route['boq-mapping/(:num)/items/(:num)'] = 'boq_mapping/item/$1/$2';
$route['boq-mapping/(:num)/items/(:num)/candidates'] = 'boq_mapping/add_candidate/$1/$2';
$route['boq-mapping/(:num)/items/(:num)/candidates/(:num)/status'] = 'boq_mapping/candidate_status/$1/$2/$3';
$route['boq-mapping/(:num)/items/(:num)/candidates/(:num)/select'] = 'boq_mapping/select/$1/$2/$3';
$route['boq-mapping/(:num)/items/(:num)/action/(confirm|reject|reopen)'] = 'boq_mapping/action/$1/$2/$3';
$route['benchmarking'] = 'benchmarking/index';
$route['cost-intelligence'] = 'cost_intelligence/index';
$route['cost-intelligence/suggestions'] = 'cost_intelligence/suggestions';
$route['ml-governance'] = 'ml_governance/index';
$route['ml-governance/datasets/create'] = 'ml_governance/create_dataset';
$route['ml-governance/datasets/(:num)'] = 'ml_governance/dataset/$1';
$route['ml-governance/datasets/(:num)/versions/create'] = 'ml_governance/create_version/$1';
$route['ml-governance/versions/(:num)'] = 'ml_governance/version/$1';
$route['ml-governance/versions/(:num)/records/(:num)/review'] = 'ml_governance/review/$1/$2';
$route['ml-governance/versions/(:num)/freeze'] = 'ml_governance/freeze/$1';
$route['ml-governance/versions/(:num)/approve'] = 'ml_governance/approve/$1';
$route['references'] = 'references/index';
$route['references/([a-z0-9-]+)'] = 'references/index/$1';
$route['references/([a-z0-9-]+)/create'] = 'references/create/$1';
$route['references/([a-z0-9-]+)/(:num)/edit'] = 'references/edit/$1/$2';
$route['references/([a-z0-9-]+)/(:num)/status'] = 'references/status/$1/$2';
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['account'] = 'account/index';
$route['account/password'] = 'account/password';
$route['roles'] = 'roles/index';
$route['roles/create'] = 'roles/create';
$route['roles/(:num)/edit'] = 'roles/edit/$1';
$route['roles/(:num)/permissions'] = 'roles/permissions/$1';
$route['permissions'] = 'permissions/index';
$route['user-roles'] = 'user_roles/index';
$route['user-roles/(:num)'] = 'user_roles/edit/$1';
$route['health'] = 'health/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
