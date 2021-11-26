<?php
defined('CORE_PATH') OR exit('No direct script access allowed');
?>
<p>The CAPTCHA Helper contains functions that assist in creating CAPTCHA images.</p>

<p>If you would like to edit this view(demo page), it's located at:</p>
<code>/application/views/demo/captcha.php</code>

<p>The corresponding controller for this page is found at:</p>		<code>/application/controllers/Demo.php</code>

<p>The corresponding view method for this page is:</p>		<code>/application/controllers/Demo::captcha</code>

<h4>Below is the demo of the Ceramic CAPTCHA Helper:</h4>
<?= $captcha['image']; ?>
<form method="post">
	<input type="text" name="captcha">
	<input type="hidden" value="<?php echo $captcha['word'] ?>" name="code">
	<input type="submit" name="submit" value="Send">
</form>