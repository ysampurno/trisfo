	<div class="copyright">
		<?php $footer = $setting_app['footer_login'] ? str_replace('{{YEAR}}', date('Y'), $setting_app['footer_login']) : '';
		echo html_entity_decode($footer);
		?>
	</div>
	</div><!-- login container -->
</body>
</html>