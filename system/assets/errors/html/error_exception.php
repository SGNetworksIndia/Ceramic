<?php
defined('CORE_PATH') OR exit('No direct script access allowed');
$exceptions = array(E_ERROR => "error", E_WARNING => 'warning', E_NOTICE => 'notice');
$colors = array("notice" => "#004f9e", "warning" => "#9b6f01", "error" => "#990000", "typeerror" => "#990000");
$bgcolors = array("notice" => "#007bff", "warning" => "#ffc107", "error" => "#f13646", "typeerror" => "#f13646");
$type = get_parent_class($exception);
$type = empty($type) ? get_class($exception) : $type;
$type = strtolower($type);
if(preg_match('/(error|warning|notice)/i', $type, $match)) {
	$type = strtolower($match[1]);
} else
	$type = strtolower($type);
$color = $colors[$type];
$bgcolor = $bgcolors[$type];
?>

<div style="border:1px solid transparent; margin-bottom: 10px;">
	<h3 style="background-color: <?= $bgcolor; ?>; color: <?= $color; ?>; padding: 15px; margin: 0; border-radius: 10px 10px 0 0;">An uncaught Exception has encountered</h3>
	<div style="border:1px solid <?= $bgcolor; ?>; border-radius: 0 0 10px 10px;">
		<div style="padding-left:20px;">
			<p><b>Type:</b> <?php echo get_class($exception); ?></p>
			<p><b>Message:</b> <?php echo $message; ?></p>
			<p><b>Filename:</b> <?php echo $exception->getFile(); ?></p>
			<p><b>Line Number:</b> <?php echo $exception->getLine(); ?></p>
		</div>
		<?php if(defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true): ?>
			<hr style="border: 1px solid #eee; box-shadow: none; background: none;">
			<div style="padding-left:20px;">
				<b>Backtrace:</b>
				<?php foreach($exception->getTrace() as $error): ?>
					<?php if(isset($error['file']) && strpos($error['file'], realpath(CORE_PATH)) !== 0): ?>
						<?php $pointer = (basename($error['file'])==basename($exception->getFile()))?"<span style='color: {$color}'>*</span>":""; ?>
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