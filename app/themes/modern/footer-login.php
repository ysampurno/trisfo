	<div class="copyright">
		<?php
			$footer_login = $setting_app['footer_login'] ? str_replace('{{YEAR}}', date('Y'), $setting_app['footer_login']) : '';
			echo html_entity_decode($footer_login);
		?>
	</div>
	</div><!-- login container -->
</body>
</html>