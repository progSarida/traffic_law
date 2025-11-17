<?php
$_POST['dir'] = rtrim(urldecode($_POST['dir']), "/").'/';
$root = isset($root) ? $root : '';
$folders = $files = array();

if (file_exists($root . $_POST['dir'])) {
    $foundFiles = array_diff(scandir($root . $_POST['dir']), array('.', '..'));
    natcasesort($foundFiles);
    
    foreach ($foundFiles as $fileEntry) {
        $filePath = $root . $_POST['dir'] . $fileEntry;
        if (file_exists($filePath)){
            // All dirs
            if(is_dir($filePath)){
                $folders[]['Path'] = htmlentities($_POST['dir'] . $fileEntry);
                $folders[count($folders)-1]['Name'] = htmlentities($fileEntry);
            }
            // All files
            else {
                $files[]['Path'] = htmlentities($fileEntry);
                $files[count($files)-1]['FullPath'] = htmlentities($_POST['dir'] . $fileEntry);
                $files[count($files)-1]['Name'] = htmlentities($fileEntry);
                $files[count($files)-1]['Ext'] = preg_replace('/^.*\./', '', $fileEntry);
            }
        }
    }
}
?>
<ul class="jqueryFileTree" style="display: none;">
	<?php foreach($folders as $folder): ?>
		<li class="directory collapsed">
			<a style="color: white" href="#" rel="<?= $folder['Path'] ?>/"><?= $folder['Name'] ?></a>
		</li>
		<br>
	<?php endforeach; ?>
	<?php foreach($files as $file): ?>
		<div style="display: inline">
    		<li class="file ext_<?= $file['Ext'] ?>">
    			<a style="color: white" href="#" rel="<?= $file['FullPath'] ?>"><?= $file['Name']; ?></a>
    			<?php if(isset($_GET['insert'])): ?>
    				<input type="checkbox" name="checked[]" style="height: 14px;width: 14px;" value="<?= isset($_GET['fullpath']) ? $file['FullPath'] : $file['Path']; ?>">
    			<?php endif; ?>
    		</li>
    		<?php if(isset($_GET['delete'])): ?>
    			<a style="color: red" href="#" class="delete-file" file-path="<?= $file['FullPath'] ?>">x</a>
    		<?php endif; ?>
		</div>
		<br>
	<?php endforeach; ?>
</ul>
