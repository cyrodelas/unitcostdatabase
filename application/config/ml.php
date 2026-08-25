<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$configured=getenv('UCD_ML_ARTIFACT_PATH');
$config['artifact_path']=($configured!==FALSE&&trim($configured)!=='')?rtrim(trim($configured),'\\/'):dirname(dirname(FCPATH)).DIRECTORY_SEPARATOR.'nexus_ucd_ml_artifacts';
$service_url=getenv('UCD_ML_SERVICE_URL');
$service_token=getenv('UCD_ML_SERVICE_TOKEN');
$config['service_url']=$service_url!==FALSE?trim($service_url):'';
$config['service_token']=$service_token!==FALSE?trim($service_token):'';
$config['connect_timeout_ms']=500;
$config['inference_timeout_ms']=2000;
