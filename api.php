<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getDefaultDashletConfig")) {

	function getDefaultDashletConfig() {
		return [
				"title"=>"",
				"descs"=>"",
				"photo"=>"",
				"author"=>false,
				"market"=>[
					"mid"=>false,
					"dependency"=>false,
				],
				"preload"=>[
					"module"=>[],
					"css"=>[],
					"js"=>[],
				],
				"type"=>false,
				"source"=>false,
				"config"=>[],
				"schema"=>[],
				"column"=>6,
				"forcenewrow"=>false,
				"header"=>true,
				"footer"=>false,
				"active"=>true,
				"containerClass"=>""
			];
	}

	function getDashConfigEditor($key,$config) {
		if(!is_array($config)) {
			$config=[
					"type"=>"text",
					"value"=>$config
				];
		}
		if(!isset($config['type'])) $config['type']="";
		if(!isset($config['value'])) $config['value']="";

		$html="";

		switch ($config['type']) {
			case 'select':
				$html.="<select name='$key' data-value='{$config['value']}' class='form-control'>";
				if(isset($config['options'])) {
					if(!is_array($config['options'])) {
						$config['options']=explode(",", $config['options']);
						foreach ($config['options'] as $key => $value) {
							if($value==$config['value'])
								$html.="<option value='$value' selected>"._ling($value)."</option>";
							else
								$html.="<option value='$value'>"._ling($value)."</option>";
						}
					} else {
						foreach ($config['options'] as $key => $value) {
							if($key==$config['value'])
								$html.="<option value='$key' selected>"._ling($value)."</option>";
							else
								$html.="<option value='$key'>"._ling($value)."</option>";
						}
					}
				}
				$html.="</select>";
				break;
			case 'dataSelector':
				$html.="<select name='$key' data-value='{$config['value']}' class='form-control'>";
				$html.=createDataSelector($config['options']);
				$html.="</select>";
				break;
			default:
				$html.="<input name='$key' type='text' class='form-control' data-value='{$config['value']}' value='{$config['value']}' />";
				break;
		}

		return $html;
	}

	function printDashlet($dashkey, $dashletConfig) {
		$dashlet=array_merge(getDefaultDashletConfig(),$dashletConfig);

		?>
			<div data-dashkey='<?=$dashkey?>' class='dashletContainer col-xs-12 col-sm-12 col-md-<?=$dashlet['column']?> col-lg-<?=$dashlet['column']?> <?=$dashlet['forcenewrow']?"clear-left":''?> <?=$dashlet['containerClass']?>'>
				<div class="dashletPanel <?=$dashlet['active']?"active":''?> panel panel-default ajaxloading ajaxloading8">
					<?php if($dashlet['header']===true) { ?>
					<div class="panel-heading">
						<div class="dashletOption dashletHandle glyphicon <?=$dashlet['active']?"glyphicon-triangle-top":'glyphicon-triangle-bottom'?> pull-left"></div>

						<div class="dashletOption dashletRemove glyphicon glyphicon-remove pull-right" cmd='remove'></div>
						<div class="dashletOption dashletSettings glyphicon glyphicon-cog pull-right" cmd='settings'></div>
						<div class="dashletOption dashletFocus glyphicon glyphicon-eye-open pull-right" cmd='focus'></div>

						<h3 class="panel-title"><?=$dashlet['title']?></h3>
					</div>
					<?php 
						} elseif(is_file(APPROOT.$dashlet['header'])) { 
							echo '<div class="panel-heading">';
							include_once APPROOT.$dashlet['header'];
							echo '</div>';
						} elseif(is_file($dashlet['header'])) { 
							echo '<div class="panel-heading">';
							include_once $dashlet['header'];
							echo '</div>';
						} 
					?>
					
					<div class="panel-body">
					<?php 
						if(!isset($dashlet['config'])) $dashlet['config']=[];
						echo getDashletBody($dashlet['type'],$dashlet['source'],$dashlet['config']);
					?>
					</div>
					
					<table class="panel-options table"> <tbody>
						<tr> <th>Width (1-10)</th> <td><select name='column' data-value='<?=$dashlet['column']?>' class='form-control'>
							<?php
								for ($col=1; $col <= 12; $col++) {
									$colText="Columns";
									if($col==1) $colText="Column";
									if($col==$dashlet['column']) {
										echo "<option value='$col' selected>$col $colText</option>";
									} else {
										echo "<option value='$col'>$col $colText</option>";
									}
								}
							?>
						</select></td> </tr>
						<tr> <th>Force New Row</th> <td><select name='forcenewrow' data-value='<?=$dashlet['forcenewrow']?>' class='form-control'>
							<?php
								if($dashlet['forcenewrow']) {
									echo "<option value='true' selected>Yes</option><option value='false'>No</option>";
								} else {
									echo "<option value='true'>Yes</option><option value='false' selected>No</option>";
								}
							?>
						</select></td> </tr>
						<?php
							if(!isset($dashlet['schema'])) $dashlet['schema']=[];
							foreach ($dashlet['config'] as $key => $value) {
								if(isset($dashlet['schema'][$key])) {
									$config=$dashlet['schema'][$key];
								} else {
									$config=[];
								}
								$title=$key;
								if(isset($config['title'])) $title=$config['title'];
								else $title=toTitle(_ling($title));

								$config['value']=$value;
								$config['title']=$title;

								echo "<tr><th>$title</th><td>";
								echo getDashConfigEditor($key,$config);
								echo "</td></tr>";
							}
						?>
					</tbody> </table>

					<?php 
						if($dashlet['footer']) {
							if(is_file(APPROOT.$dashlet['footer'])) { 
								echo '<div class="panel-footer">';
								include_once APPROOT.$dashlet['footer'];
								echo '</div>';
							} elseif(is_file($dashlet['footer'])) { 
								echo '<div class="panel-footer">';
								include_once $dashlet['footer'];
								echo '</div>';
							} elseif(strlen($dashlet['footer'])>2) {
								echo '<div class="panel-footer">';
								echo $dashlet['footer'];
								echo '</div>';
							}
						}
					?>
				</div>
			</div>
		<?php
	}

	function getDashletBody($type, $source, $dashletConfig=[]) {
		//echo "$type $source";
		switch ($type) {
			case 'widget':
				loadWidget($source,$dashletConfig);
				break;
			
			case 'module':
				loadModule($source);
				break;

			case 'iframe':
				if(!(substr($source, 0,7)=="http://" || substr($source, 0,8)=="https://")) {
					$source=_link($source);
				}
				echo "<iframe src='{$source}' allowTransparency='true' frameborder='0' scrolling='auto'></iframe>";
				break;

			case 'script':
				echo "<script src=\"{$source}\"></script>";
				break;

			case 'php':
				if(file_exists(APPROOT.$source)) {
					include APPROOT.$source;
				} else {
					echo "<h3 align=center>Dashlet Source Not Found</h3>";
				}
				break;

			default:
				echo "<h3 align=center>Dashlet '$type' Not Supported</h3>";
				break;
		}
	}

	function listDashlets($recache=false) {
		$paths=getLoaderFolders('pluginPaths',"dashlets");
		$currentDashlets=getUserConfig("dashboard")['dashlets'];

		$loadedDashlets=[];
		foreach ($currentDashlets as $dash) {
			if(isset($dash['dashid'])) {
				if(isset($loadedDashlets[$dash['dashid']])) $loadedDashlets[$dash['dashid']]++;
				else $loadedDashlets[$dash['dashid']]=1;
			}
		};

		$dashlets=[];
		foreach ($paths as $dir) {
			$dir=ROOT.$dir;
			if(!is_dir($dir)) continue;

			$fs=scandir($dir);
			$fs=array_splice($fs,2);
			foreach ($fs as $fx) {
				$file=$dir.$fx;
				$extension = pathinfo($file, PATHINFO_EXTENSION);
				if($extension=="json") {
					$dKey=str_replace(".json", '', $fx);

					$dashConfig=[
								"title"=>toTitle($dKey),
								"source"=>$file,
								"active"=>0,
							];
					if(array_key_exists($dKey, $loadedDashlets)) {
						$dashConfig['active']=$loadedDashlets[$dKey];
					}
					$dashlets[$dKey]=$dashConfig;
				}
			}
		}
		return $dashlets;
	}
}
?>