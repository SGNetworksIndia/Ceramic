<?php
defined('CORE_PATH') OR exit('No direct script access allowed');
?>
<p>
	The Template Engine allows to create & manipulate PHP templates (*.tpl, *.*) and allows to create dynamic page without any hustles.
	<br>
	It also allows you to use PHP codes inside the template if the template saved as *.php file.
	<br>
	<br>
	Right now only variables printing are supported. </p>

<p>Syntax:</p>
<code>{{var}}</code>

<p>If you would like to edit this view(demo page), it's located at:</p>
<code>/application/views/demo/templating.php</code>

<p>The corresponding controller for this page is found at:</p>
<code>/application/controllers/Demo.php</code>

<p>The corresponding view method for this page is:</p>
<code>/application/controllers/Demo::templating</code>

<h4>Below is the demo of the Ceramic Template Engine:</h4>
Hello! My name is {{name}}. I'm a {{occupation}}. I'm continuing to learn {{product}} and I love to do that.
I'm currently viewing the demonstration of {{product}} {{feature}} to learn more about it.
I've learned that, I can print any php variable inside a static page whether it's HTML or XML or any others.
I've also learned that, the {{product}} {{feature}} allows us to change the value of each data by modifying it's dataset located in the {{product}} Controller.
