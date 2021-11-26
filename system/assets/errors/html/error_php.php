<?php
defined('CORE_PATH') OR exit('No direct script access allowed');
$colors = array("notice"=>"#004f9e","warning"=>"#ecaf07","error"=>"#990000");
$bgcolors = array("notice"=>"#007bff","warning"=>"#ffc107","error"=>"#f13646");
$type = preg_replace("/^(user )(\w+)$/i", "$2", $severity);
$severity = preg_replace("/^(user )(\w+)$/i", "Non-Enforcing $2", $severity);

if(preg_match('/(error|warning|notice)/i', $type, $match)) {
	$type = strtolower($match[1]);
} else
	$type = strtolower($type);
$color = $colors[$type];
$bgcolor = $bgcolors[$type];
?>

<div style="border:1px solid transparent; margin-bottom: 10px;" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">

	<h3 style="background-color: <?=$bgcolor;?>; padding: 15px; margin: 0; border-radius: 10px 10px 0 0;">A PHP <?=$severity;?> has encountered</h3>

	<div style="border:1px solid <?=$bgcolor;?>; border-radius: 0 0 10px 10px;">
		<div style="padding-left:20px;">
			<p><b>Severity:</b> <?php echo $severity; ?></p>
			<p><b>Message:</b>  <?php echo $message; ?></p>
			<p><b>Filename:</b> <?php echo $filepath; ?></p>
			<p><b>Line Number:</b> <?php echo $line; ?></p>
		</div>
		<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>
			<hr style="border: 1px solid #eee; box-shadow: none; background: none;">
			<div style="padding-left:20px;">
				<b>Backtrace:</b>
				<?php foreach (debug_backtrace() as $error): ?>
					<?php if (isset($error['file']) && strpos($error['file'], realpath(CORE_PATH)) !== 0): ?>
						<?php $pointer = (basename($error['file'])==basename($filepath))?"<span style='color: {$color}'>*</span>":""; ?>
						<p style="margin-left:10px">
							<b><?=$pointer?>File:</b> <?php echo $error['file'] ?><br />
							<b><?=$pointer?>Line:</b> <?php echo $error['line'] ?><br />
							<b><?=$pointer?>Function:</b> <?php echo $error['function'] ?>
						</p>
					<?php endif ?>
				<?php endforeach ?>
			</div>
		<?php endif ?>
	</div>
</div>