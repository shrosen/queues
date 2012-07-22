#!/usr/bin/php -q
<?php
//include freepbx configuration 
$restrict_mods = true;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}
list($agi, $vars) = __agi();

$get = array(
		'CALLBACKNUM',
		'QPOS',
		'QUEUE',
		'CustID'
);

foreach ($get as $g) {
	$vars[$g] = agi_get_var($g);
}

if ($vars['CustID'] >=1){$CustomerID='[L'.$vars['CustID'].']';} 

//dbug('var', $vars);
//dbug('$CustomerID', $CustomerID);
//dbug('$CustID', $CustID);

$o = array(
		'Channel'		=> 'Local/' . $vars['CALLBACKNUM']. '@Call2Q',
		'CallerID'		=> $CustomerID.' cbq: '.$vars['CALLBACKNUM'].'<'.$vars['CALLBACKNUM'].'>',
		'Async'			=> 'true',
		'Variable'		=> array(
								'QUEUE' => $vars['QUEUE'],
								'QPOS' => $vars['QPOS'],
                'CALLBACKNUM' => $vars['CALLBACKNUM'],				
                'CustID' => $vars['CustID'],
                'QPARAM' => 'R',
                'ANNOUNCEOVER' => '""'                                
							),
		'Extension'		=> 's',
		'Priority'		=> '1',
		'Context'		=> 'cb2'
);
//dbug('originate', $o);
  $astman->Originate($o);
//dbug('ret',$ret);


function agi_get_var($value) {
	global $agi;
	$r = $agi->get_variable($value);
	
	if ($r['result'] == 1) {
		$result = $r['data'];
		return $result;
	}
	return '';
}

function __agi(){
	require_once('phpagi.php');
	$agi=new AGI();
	foreach($agi->request as $key => $value){//strip agi_ prefix from keys
		if(substr($key,0,4)=='agi_'){
			$opts[substr($key,4)]=$value;
		}
	}

	foreach($opts as $key => $value){//get passed in vars
		if(substr($key,0,4)=='arg_'){
			$expld=explode('=',$value);
			$opts[$expld[0]]=$expld[1];
			unset($opts[$key]);
		}
	}
	
	array_shift($_SERVER['argv']);
	foreach($_SERVER['argv'] as $arg){
		$arg=explode('=',$arg);
		//remove leading '--'
		if(substr($arg['0'],0,2) == '--'){$arg['0']=substr($arg['0'],2);}
		$opts[$arg['0']]=isset($arg['1'])?$arg['1']:null;
	}

	return array($agi, $opts);
}


?>
